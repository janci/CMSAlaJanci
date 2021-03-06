<?php

/**
 * This file is part of the Nette Framework (http://nette.org)
 *
 * Copyright (c) 2004 David Grudl (http://davidgrudl.com)
 *
 * For the full copyright and license information, please view
 * the file license.txt that was distributed with this source code.
 * @package Nette\Latte\Macros
 */



/**
 * Macros for NUI.
 *
 * - {link destination ...} control link
 * - {plink destination ...} presenter link
 * - {snippet ?} ... {/snippet ?} control snippet
 * - {contentType ...} HTTP Content-Type header
 * - {status ...} HTTP status
 *
 * @author     David Grudl
 * @package Nette\Latte\Macros
 */
class NUIMacros extends NMacroSet
{
	/** @internal PHP identifier */
	const RE_IDENTIFIER = '[_a-zA-Z\x7F-\xFF][_a-zA-Z0-9\x7F-\xFF]*';

	/** @var array */
	private $namedBlocks = array();

	/** @var bool */
	private $extends;



	public static function install(NLatteCompiler $compiler)
	{
		$me = new self($compiler);
		$me->addMacro('include', array($me, 'macroInclude'));
		$me->addMacro('includeblock', array($me, 'macroIncludeBlock'));
		$me->addMacro('extends', array($me, 'macroExtends'));
		$me->addMacro('layout', array($me, 'macroExtends'));
		$me->addMacro('block', array($me, 'macroBlock'), array($me, 'macroBlockEnd'));
		$me->addMacro('#', array($me, 'macroBlock'), array($me, 'macroBlockEnd'));
		$me->addMacro('define', array($me, 'macroBlock'), array($me, 'macroBlockEnd'));
		$me->addMacro('snippet', array($me, 'macroBlock'), array($me, 'macroBlockEnd'));
		$me->addMacro('ifset', array($me, 'macroIfset'), 'endif');

		$me->addMacro('widget', array($me, 'macroControl')); // deprecated - use control
		$me->addMacro('control', array($me, 'macroControl'));

		$me->addMacro('href', NULL, NULL, create_function('NMacroNode $node, NPhpWriter $writer', 'extract(NCFix::$vars['.NCFix::uses(array('me'=>$me)).'], EXTR_REFS);
			return \' ?> href="<?php \' . $me->macroLink($node, $writer) . \' ?>"<?php \';
		'));
		$me->addMacro('plink', array($me, 'macroLink'));
		$me->addMacro('link', array($me, 'macroLink'));
		$me->addMacro('ifCurrent', array($me, 'macroIfCurrent'), 'endif'); // deprecated; use n:class="$presenter->linkCurrent ? ..."

		$me->addMacro('contentType', array($me, 'macroContentType'));
		$me->addMacro('status', array($me, 'macroStatus'));
	}



	/**
	 * Initializes before template parsing.
	 * @return void
	 */
	public function initialize()
	{
		$this->namedBlocks = array();
		$this->extends = NULL;
	}



	/**
	 * Finishes template parsing.
	 * @return array(prolog, epilog)
	 */
	public function finalize()
	{
		// try close last block
		$last = $this->getCompiler()->getMacroNode();
		if ($last && ($last->name === 'block' || $last->name === '#')) {
			$this->getCompiler()->writeMacro('/' . $last->name);
		}

		$epilog = $prolog = array();

		if ($this->namedBlocks) {
			foreach ($this->namedBlocks as $name => $code) {
				$func = '_lb' . substr(md5($this->getCompiler()->getTemplateId() . $name), 0, 10) . '_' . preg_replace('#[^a-z0-9_]#i', '_', $name);
				$snippet = $name[0] === '_';
				$prolog[] = "//\n// block $name\n//\n"
					. "if (!function_exists(\$_l->blocks[" . var_export($name, TRUE) . "][] = '$func')) { "
					. "function $func(\$_l, \$_args) { "
					. (PHP_VERSION_ID > 50208 ? 'extract($_args)' : 'foreach ($_args as $__k => $__v) $$__k = $__v') // PHP bug #46873
					. ($snippet ? '; $_control->validateControl(' . var_export(substr($name, 1), TRUE) . ')' : '')
					. "\n?>$code<?php\n}}";
			}
			$prolog[] = "//\n// end of blocks\n//";
		}

		if ($this->namedBlocks || $this->extends) {
			$prolog[] = "// template extending and snippets support";

			$prolog[] = '$_l->extends = '
				. ($this->extends ? $this->extends : 'empty($template->_extended) && isset($_control) && $_control instanceof NPresenter ? $_control->findLayoutTemplateFile() : NULL')
				. '; $template->_extended = $_extended = TRUE;';

			$prolog[] = '
if ($_l->extends) {
	' . ($this->namedBlocks ? 'ob_start();' : 'return NCoreMacros::includeTemplate($_l->extends, get_defined_vars(), $template)->render();') . '

} elseif (!empty($_control->snippetMode)) {
	return NUIMacros::renderSnippets($_control, $_l, get_defined_vars());
}';
		} else {
			$prolog[] = '
// snippets support
if (!empty($_control->snippetMode)) {
	return NUIMacros::renderSnippets($_control, $_l, get_defined_vars());
}';
		}

		return array(implode("\n\n", $prolog), implode("\n", $epilog));
	}



	/********************* macros ****************d*g**/



	/**
	 * {include #block}
	 */
	public function macroInclude(NMacroNode $node, NPhpWriter $writer)
	{
		$destination = $node->tokenizer->fetchWord(); // destination [,] [params]
		if (substr($destination, 0, 1) !== '#') {
			return FALSE;
		}

		$destination = ltrim($destination, '#');
		if (!NStrings::match($destination, '#^\$?' . self::RE_IDENTIFIER . '$#')) {
			throw new NCompileException("Included block name must be alphanumeric string, '$destination' given.");
		}

		$parent = $destination === 'parent';
		if ($destination === 'parent' || $destination === 'this') {
			for ($item = $node->parentNode; $item && $item->name !== 'block' && !isset($item->data->name); $item = $item->parentNode);
			if (!$item) {
				throw new NCompileException("Cannot include $destination block outside of any block.");
			}
			$destination = $item->data->name;
		}

		$name = $destination[0] === '$' ? $destination : var_export($destination, TRUE);
		if (isset($this->namedBlocks[$destination]) && !$parent) {
			$cmd = "call_user_func(reset(\$_l->blocks[$name]), \$_l, %node.array? + get_defined_vars())";
		} else {
			$cmd = 'NUIMacros::callBlock' . ($parent ? 'Parent' : '') . "(\$_l, $name, %node.array? + " . ($parent ? 'get_defined_vars' : '$template->getParameters') . '())';
		}

		if ($node->modifiers) {
			return $writer->write("ob_start(); $cmd; echo %modify(ob_get_clean())");
		} else {
			return $writer->write($cmd);
		}
	}



	/**
	 * {includeblock "file"}
	 */
	public function macroIncludeBlock(NMacroNode $node, NPhpWriter $writer)
	{
		return $writer->write('NCoreMacros::includeTemplate(%node.word, %node.array? + get_defined_vars(), $_l->templates[%var])->render()',
			$this->getCompiler()->getTemplateId());
	}



	/**
	 * {extends auto | none | $var | "file"}
	 */
	public function macroExtends(NMacroNode $node, NPhpWriter $writer)
	{
		if (!$node->args) {
			throw new NCompileException("Missing destination in {extends}");
		}
		if (!empty($node->parentNode)) {
			throw new NCompileException("{extends} must be placed outside any macro.");
		}
		if ($this->extends !== NULL) {
			throw new NCompileException("Multiple {extends} declarations are not allowed.");
		}
		if ($node->args === 'none') {
			$this->extends = 'FALSE';
		} elseif ($node->args === 'auto') {
			$this->extends = '$_presenter->findLayoutTemplateFile()';
		} else {
			$this->extends = $writer->write('%node.word%node.args');
		}
		return;
	}



	/**
	 * {block [[#]name]}
	 * {snippet [name [,]] [tag]}
	 * {define [#]name}
	 */
	public function macroBlock(NMacroNode $node, NPhpWriter $writer)
	{
		$name = $node->tokenizer->fetchWord();

		if ($node->name === 'block' && $name === FALSE) { // anonymous block
			return $node->modifiers === '' ? '' : 'ob_start()';
		} elseif ($node->name === '#') {
			$node->modifiers = substr($node->modifiers, 0, -7); // remove |escape, dirty fix
		}

		$node->data->name = $name = ltrim($name, '#');
		if ($name == NULL) {
			if ($node->name !== 'snippet') {
				throw new NCompileException("Missing block name.");
			}

		} elseif (!NStrings::match($name, '#^' . self::RE_IDENTIFIER . '$#')) { // dynamic blok/snippet
			if ($node->name === 'snippet') {
				for ($parent = $node->parentNode; $parent && $parent->name !== 'snippet'; $parent = $parent->parentNode);
				if (!$parent) {
					throw new NCompileException("Dynamic snippets are allowed only inside static snippet.");
				}
				$parent->data->dynamic = TRUE;
				$node->data->leave = TRUE;
				$node->closingCode = "<?php \$_dynSnippets[\$_dynSnippetId] = ob_get_flush() ?>";

				if ($node->htmlNode) {
					$node->attrCode = $writer->write("<?php echo ' id=\"' . (\$_dynSnippetId = \$_control->getSnippetId({$writer->formatWord($name)})) . '\"' ?>");
					return $writer->write('ob_start()');
				}
				$tag = trim($node->tokenizer->fetchWord(), '<>');
				$tag = $tag ? $tag : 'div';
				$node->closingCode .= "\n</$tag>";
				return $writer->write("?>\n<$tag id=\"<?php echo \$_dynSnippetId = \$_control->getSnippetId({$writer->formatWord($name)}) ?>\"><?php ob_start()");

			} else {
				$node->data->leave = TRUE;
				$fname = $writer->formatWord($name);
				$node->closingCode = "<?php }} call_user_func(reset(\$_l->blocks[$fname]), \$_l, get_defined_vars()) ?>";
				$func = '_lb' . substr(md5($this->getCompiler()->getTemplateId() . $name), 0, 10) . '_' . preg_replace('#[^a-z0-9_]#i', '_', $name);
				return "//\n// block $name\n//\n"
					. "if (!function_exists(\$_l->blocks[$fname][] = '$func')) { "
					. "function $func(\$_l, \$_args) { "
					. (PHP_VERSION_ID > 50208 ? 'extract($_args)' : 'foreach ($_args as $__k => $__v) $$__k = $__v'); // PHP bug #46873
			}
		}

		// static blok/snippet
		if ($node->name === 'snippet') {
			$node->data->name = $name = '_' . $name;
		}
		if (isset($this->namedBlocks[$name])) {
			throw new NCompileException("Cannot redeclare static block '$name'");
		}
		$prolog = $this->namedBlocks ? '' : "if (\$_l->extends) { ob_end_clean(); return NCoreMacros::includeTemplate(\$_l->extends, get_defined_vars(), \$template)->render(); }\n";
		$top = empty($node->parentNode);
		$this->namedBlocks[$name] = TRUE;

		$include = 'call_user_func(reset($_l->blocks[%var]), $_l, ' . ($node->name === 'snippet' ? '$template->getParameters()' : 'get_defined_vars()') . ')';
		if ($node->modifiers) {
			$include = "ob_start(); $include; echo %modify(ob_get_clean())";
		}

		if ($node->name === 'snippet') {
			if ($node->htmlNode) {
				$node->attrCode = $writer->write('<?php echo \' id="\' . $_control->getSnippetId(%var) . \'"\' ?>', (string) substr($name, 1));
				return $writer->write($prolog . $include, $name);
			}
			$tag = trim($node->tokenizer->fetchWord(), '<>');
			$tag = $tag ? $tag : 'div';
			return $writer->write("$prolog ?>\n<$tag id=\"<?php echo \$_control->getSnippetId(%var) ?>\"><?php $include ?>\n</$tag><?php ",
				(string) substr($name, 1), $name
			);

		} elseif ($node->name === 'define') {
			return $prolog;

		} else {
			return $writer->write($prolog . $include, $name);
		}
	}



	/**
	 * {/block}
	 * {/snippet}
	 * {/define}
	 */
	public function macroBlockEnd(NMacroNode $node, NPhpWriter $writer)
	{
		if (isset($node->data->name)) { // block, snippet, define
			if ($node->name === 'snippet' && $node->htmlNode && $node->prefix === NMacroNode::PREFIX_NONE // n:snippet -> n:inner-snippet
				&& preg_match("#^.*? n:\w+>\n?#s", $node->content, $m1) && preg_match("#[ \t]*<[^<]+$#sD", $node->content, $m2))
			{
				$node->openingCode = $m1[0] . $node->openingCode;
				$node->content = substr($node->content, strlen($m1[0]), -strlen($m2[0]));
				$node->closingCode .= $m2[0];
			}

			if (empty($node->data->leave)) {
				if (!empty($node->data->dynamic)) {
					$node->content .= '<?php if (isset($_dynSnippets)) return $_dynSnippets; ?>';
				}
				$this->namedBlocks[$node->data->name] = $tmp = rtrim(ltrim($node->content, "\n"), " \t");
				$node->content = substr_replace($node->content, $node->openingCode . "\n", strspn($node->content, "\n"), strlen($tmp));
				$node->openingCode = "<?php ?>";
			}

		} elseif ($node->modifiers) { // anonymous block with modifier
			return $writer->write('echo %modify(ob_get_clean())');
		}
	}



	/**
	 * {ifset #block}
	 */
	public function macroIfset(NMacroNode $node, NPhpWriter $writer)
	{
		if (!NStrings::contains($node->args, '#')) {
			return FALSE;
		}
		$list = array();
		while (($name = $node->tokenizer->fetchWord()) !== FALSE) {
			$list[] = $name[0] === '#' ? '$_l->blocks["' . substr($name, 1) . '"]' : $name;
		}
		return 'if (isset(' . implode(', ', $list) . ')):';
	}



	/**
	 * {control name[:method] [params]}
	 */
	public function macroControl(NMacroNode $node, NPhpWriter $writer)
	{
		$pair = $node->tokenizer->fetchWord();
		if ($pair === FALSE) {
			throw new NCompileException("Missing control name in {control}");
		}
		$pair = explode(':', $pair, 2);
		$name = $writer->formatWord($pair[0]);
		$method = isset($pair[1]) ? ucfirst($pair[1]) : '';
		$method = NStrings::match($method, '#^(' . self::RE_IDENTIFIER . '|)$#') ? "render$method" : "{\"render$method\"}";
		$param = $writer->formatArray();
		if (!NStrings::contains($node->args, '=>')) {
			$param = substr($param, 6, -1); // removes array()
		}
		return ($name[0] === '$' ? "if (is_object($name)) \$_ctrl = $name; else " : '')
			. '$_ctrl = $_control->getComponent(' . $name . '); '
			. 'if ($_ctrl instanceof IRenderable) $_ctrl->validateControl(); '
			. "\$_ctrl->$method($param)";
	}



	/**
	 * {link destination [,] [params]}
	 * {plink destination [,] [params]}
	 * n:href="destination [,] [params]"
	 */
	public function macroLink(NMacroNode $node, NPhpWriter $writer)
	{
		return $writer->write('echo %escape(%modify(' . ($node->name === 'plink' ? '$_presenter' : '$_control') . '->link(%node.word, %node.array?)))');
	}



	/**
	 * {ifCurrent destination [,] [params]}
	 */
	public function macroIfCurrent(NMacroNode $node, NPhpWriter $writer)
	{
		return $writer->write(($node->args ? 'try { $_presenter->link(%node.word, %node.array?); } catch (NInvalidLinkException $e) {}' : '')
			. '; if ($_presenter->getLastCreatedRequestFlag("current")):');
	}



	/**
	 * {contentType ...}
	 */
	public function macroContentType(NMacroNode $node, NPhpWriter $writer)
	{
		if (NStrings::contains($node->args, 'xhtml')) {
			$this->getCompiler()->setContentType(NLatteCompiler::CONTENT_XHTML);

		} elseif (NStrings::contains($node->args, 'html')) {
			$this->getCompiler()->setContentType(NLatteCompiler::CONTENT_HTML);

		} elseif (NStrings::contains($node->args, 'xml')) {
			$this->getCompiler()->setContentType(NLatteCompiler::CONTENT_XML);

		} elseif (NStrings::contains($node->args, 'javascript')) {
			$this->getCompiler()->setContentType(NLatteCompiler::CONTENT_JS);

		} elseif (NStrings::contains($node->args, 'css')) {
			$this->getCompiler()->setContentType(NLatteCompiler::CONTENT_CSS);

		} elseif (NStrings::contains($node->args, 'calendar')) {
			$this->getCompiler()->setContentType(NLatteCompiler::CONTENT_ICAL);

		} else {
			$this->getCompiler()->setContentType(NLatteCompiler::CONTENT_TEXT);
		}

		// temporary solution
		if (NStrings::contains($node->args, '/')) {
			return $writer->write('$netteHttpResponse->setHeader("Content-Type", %var)', $node->args);
		}
	}



	/**
	 * {status ...}
	 */
	public function macroStatus(NMacroNode $node, NPhpWriter $writer)
	{
		return $writer->write((substr($node->args, -1) === '?' ? 'if (!$netteHttpResponse->isSent()) ' : '') .
			'$netteHttpResponse->setCode(%var)', (int) $node->args
		);
	}



	/********************* run-time writers ****************d*g**/



	/**
	 * Calls block.
	 * @return void
	 */
	public static function callBlock(stdClass $context, $name, array $params)
	{
		if (empty($context->blocks[$name])) {
			throw new InvalidStateException("Cannot include undefined block '$name'.");
		}
		$block = reset($context->blocks[$name]);
		$block($context, $params);
	}



	/**
	 * Calls parent block.
	 * @return void
	 */
	public static function callBlockParent(stdClass $context, $name, array $params)
	{
		if (empty($context->blocks[$name]) || ($block = next($context->blocks[$name])) === FALSE) {
			throw new InvalidStateException("Cannot include undefined parent block '$name'.");
		}
		$block($context, $params);
	}



	public static function renderSnippets(NControl $control, stdClass $local, array $params)
	{
		$control->snippetMode = FALSE;
		$payload = $control->getPresenter()->getPayload();
		if (isset($local->blocks)) {
			foreach ($local->blocks as $name => $function) {
				if ($name[0] !== '_' || !$control->isControlInvalid(substr($name, 1))) {
					continue;
				}
				ob_start();
				$function = reset($function);
				$snippets = $function($local, $params);
				$payload->snippets[$id = $control->getSnippetId(substr($name, 1))] = ob_get_clean();
				if ($snippets) {
					$payload->snippets += $snippets;
					unset($payload->snippets[$id]);
				}
			}
		}
		$control->snippetMode = TRUE;
		if ($control instanceof IRenderable) {
			$queue = array($control);
			do {
				foreach (array_shift($queue)->getComponents() as $child) {
					if ($child instanceof IRenderable) {
						if ($child->isControlInvalid()) {
							$child->snippetMode = TRUE;
							$child->render();
							$child->snippetMode = FALSE;
						}
					} elseif ($child instanceof IComponentContainer) {
						$queue[] = $child;
					}
				}
			} while ($queue);
		}
	}

}
