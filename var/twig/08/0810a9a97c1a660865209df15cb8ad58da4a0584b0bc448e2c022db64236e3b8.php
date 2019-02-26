<?php

/* document.twig */
class __TwigTemplate_c9f4a4c3580814fbdd4a7eba1781a9d0f2fe51f56f2addcdd3cc1e64a87f09b0 extends Twig_Template
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
        echo "<html>
<head>
\t<meta charset=\"UTF-8\">
\t<title>";
        // line 4
        echo twig_escape_filter($this->env, twig_get_attribute($this->env, $this->source, ($context["doc"] ?? null), "title", []), "html", null, true);
        echo "</title>
</head>
<body>
\t";
        // line 7
        $this->loadTemplate("config.twig", "document.twig", 7)->display($context);
        // line 8
        echo "
\t";
        // line 9
        echo ($context["content"] ?? null);
        echo "

    ";
        // line 11
        if ((twig_length_filter($this->env, ($context["urls"] ?? null)) > 0)) {
            // line 12
            echo "\t<pagebreak />
\t<a name=\"wxr2pdf-urls\"></a><h1>";
            // line 13
            echo twig_escape_filter($this->env, twig_get_attribute($this->env, $this->source, ($context["l10n"] ?? null), "external_files", []), "html", null, true);
            echo "</h1>
\t<ol>
\t";
            // line 15
            $context['_parent'] = $context;
            $context['_seq'] = twig_ensure_traversable(($context["urls"] ?? null));
            foreach ($context['_seq'] as $context["_key"] => $context["url"]) {
                // line 16
                echo "\t\t<li>";
                echo twig_escape_filter($this->env, twig_get_attribute($this->env, $this->source, $context["url"], "txt", []), "html", null, true);
                echo ":<br /><a href=\"";
                echo twig_escape_filter($this->env, twig_get_attribute($this->env, $this->source, $context["url"], "url", []), "html", null, true);
                echo "\">";
                echo twig_escape_filter($this->env, twig_get_attribute($this->env, $this->source, $context["url"], "url", []), "html", null, true);
                echo "</a></li>
\t";
            }
            $_parent = $context['_parent'];
            unset($context['_seq'], $context['_iterated'], $context['_key'], $context['url'], $context['_parent'], $context['loop']);
            $context = array_intersect_key($context, $_parent) + $_parent;
            // line 18
            echo "\t</ol>
\t";
        }
        // line 20
        echo "</body>
</html>";
    }

    public function getTemplateName()
    {
        return "document.twig";
    }

    public function isTraitable()
    {
        return false;
    }

    public function getDebugInfo()
    {
        return array (  75 => 20,  71 => 18,  58 => 16,  54 => 15,  49 => 13,  46 => 12,  44 => 11,  39 => 9,  36 => 8,  34 => 7,  28 => 4,  23 => 1,);
    }

    public function getSourceContext()
    {
        return new Twig_Source("", "document.twig", "/app/public/wp-content/plugins/wxr2pdf/templates/twig/document.twig");
    }
}
