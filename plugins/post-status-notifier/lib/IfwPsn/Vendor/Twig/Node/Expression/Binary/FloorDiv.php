<?php

/*
 * This file is part of Twig.
 *
 * (c) 2009 Fabien Potencier
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
class IfwPsn_Vendor_Twig_Node_Expression_Binary_FloorDiv extends IfwPsn_Vendor_Twig_Node_Expression_Binary
{
    /**
     * Compiles the node to PHP.
     *
     * @param IfwPsn_Vendor_Twig_Compiler A IfwPsn_Vendor_Twig_Compiler instance
     */
    public function compile(IfwPsn_Vendor_Twig_Compiler $compiler)
    {
        $compiler->raw('intval(floor(');
        parent::compile($compiler);
        $compiler->raw('))');
    }

    public function operator(IfwPsn_Vendor_Twig_Compiler $compiler)
    {
        return $compiler->raw('/');
    }
}
