<?php

/* config.twig */
class __TwigTemplate_532a330ff32c7a2912acf91264d6de537165bd23125546a558be01c35ffb535b extends Twig_Template
{
    private $source;

    public function __construct(Twig_Environment $env)
    {
        parent::__construct($env);

        $this->source = $this->getSourceContext();

        $this->parent = false;

        $this->blocks = [
        ];
    }

    protected function doDisplay(array $context, array $blocks = [])
    {
        // line 1
        echo "<htmlpageheader name=\"MyHeader1\">
<table width=\"100%\" style=\"vertical-align: bottom; font-family: serif; font-size: 8pt;
    color: #000000; font-weight: bold; font-style: italic; border-bottom: 1px solid #000000;\"><tr>
    <td width=\"50%\"><span style=\"font-weight: bold; font-style: italic;\">";
        // line 4
        echo twig_escape_filter($this->env, twig_get_attribute($this->env, $this->source, ($context["doc"] ?? null), "madeby", []));
        echo "</span></td>
    <td width=\"50%\" style=\"text-align: right; \">";
        // line 5
        echo twig_escape_filter($this->env, twig_get_attribute($this->env, $this->source, ($context["doc"] ?? null), "titleprefix", []));
        echo " ";
        echo twig_escape_filter($this->env, twig_get_attribute($this->env, $this->source, ($context["doc"] ?? null), "title", []));
        echo "</td>
    </tr></table>
</htmlpageheader>

<htmlpagefooter name=\"MyFooter1\">
<table width=\"100%\" style=\"vertical-align: bottom; font-family: serif; font-size: 8pt;
    color: #000000; font-weight: bold; font-style: italic;\"><tr>
    <td width=\"33%\"><span style=\"font-weight: bold; font-style: italic;\">{DATE ";
        // line 12
        echo twig_escape_filter($this->env, twig_get_attribute($this->env, $this->source, ($context["l10n"] ?? null), "date_format", []), "html", null, true);
        echo " }</span></td>
    <td width=\"33%\" align=\"center\" style=\"font-weight: bold; font-style: italic;\">{PAGENO}/{nbpg}</td>
    <td width=\"33%\" style=\"text-align: right; \">";
        // line 14
        echo twig_escape_filter($this->env, twig_get_attribute($this->env, $this->source, ($context["doc"] ?? null), "title", []));
        echo "</td>
    </tr></table>
</htmlpagefooter>

<sethtmlpageheader name=\"MyHeader1\" value=\"on\" show-this-page=\"1\" />
<sethtmlpagefooter name=\"MyFooter1\" value=\"on\" />";
    }

    public function getTemplateName()
    {
        return "config.twig";
    }

    public function isTraitable()
    {
        return false;
    }

    public function getDebugInfo()
    {
        return array (  49 => 14,  44 => 12,  32 => 5,  28 => 4,  23 => 1,);
    }

    public function getSourceContext()
    {
        return new Twig_Source("", "config.twig", "/app/public/wp-content/plugins/wxr2pdf/templates/twig/config.twig");
    }
}
