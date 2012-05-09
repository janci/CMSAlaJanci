<?php
interface IFilter {
    /**
     * filter for replace content
     * @return string
     */
    public function filter($content);
}
