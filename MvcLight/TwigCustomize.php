<?php

namespace MvcLight;

class TwigCustomize extends \Twig_Environment {

    public function __construct(\Twig_LoaderInterface $loader = null, $options = array()) {
        parent::__construct($loader, $options);
    }

}
