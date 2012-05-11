<?php
/**
 * This file is part of the CMSAlaJanci (https://github.com/janci/CMSAlaJanci)
 *
 * Copyright (c) 2012 Ján Švantner (http://www.janci.net)
 *
 * For the full copyright and license information, please view the file license.txt that was distributed with this source code.
 */

/**
 * Create publisher macros
 * @author Svantner Jan
 */
class PublishMacros extends NMacroSet {
    
    /**
     * Overide methods to initialize new macros for template
     * @param \Nette\Latte\Compiler $compiler
     * @return DemoMacros 
     */
    public static function install(NLatteCompiler $compiler) {
        $me = new static($compiler);
        $me->addMacro('publink','$_tmp_data = explode(":",%node.word); $_tmp_filename = array_pop($_tmp_data);  echo $presenter->getService("publisher")->wwwPath($_tmp_data["0"], $_tmp_filename);');
        return $me;
    }
}

