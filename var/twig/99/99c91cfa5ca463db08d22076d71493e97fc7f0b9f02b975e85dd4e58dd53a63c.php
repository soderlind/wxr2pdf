<?php

/* comments.twig */
class __TwigTemplate_f2931a00eace40077317c7bfccb52060a9411117623c3770378e60b0089bd7d3 extends Twig_Template
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
        echo "<hr>
<strong>";
        // line 2
        echo twig_escape_filter($this->env, twig_get_attribute($this->env, $this->source, ($context["l10n"] ?? null), "comments", []), "html", null, true);
        echo "</strong>
<dl>
";
        // line 4
        $context['_parent'] = $context;
        $context['_seq'] = twig_ensure_traversable(twig_get_attribute($this->env, $this->source, ($context["post"] ?? null), "comments", []));
        foreach ($context['_seq'] as $context["_key"] => $context["comment"]) {
            // line 5
            echo "    ";
            if (twig_get_attribute($this->env, $this->source, $context["comment"], "comment_approved", [])) {
                // line 6
                echo "    <dt><strong>";
                echo twig_escape_filter($this->env, twig_get_attribute($this->env, $this->source, $context["comment"], "comment_author", []), "html", null, true);
                echo ":</strong></dt>
        <dd>";
                // line 7
                echo twig_escape_filter($this->env, twig_get_attribute($this->env, $this->source, ($context["l10n"] ?? null), "email", []), "html", null, true);
                echo ": ";
                echo twig_escape_filter($this->env, twig_get_attribute($this->env, $this->source, $context["comment"], "comment_author_email", []), "html", null, true);
                echo "</dd>
        <dd>";
                // line 8
                echo twig_escape_filter($this->env, twig_get_attribute($this->env, $this->source, ($context["l10n"] ?? null), "url", []), "html", null, true);
                echo ": ";
                echo twig_escape_filter($this->env, twig_get_attribute($this->env, $this->source, $context["comment"], "comment_author_url", []), "html", null, true);
                echo "</dd>
        <dd>";
                // line 9
                echo twig_escape_filter($this->env, twig_get_attribute($this->env, $this->source, ($context["l10n"] ?? null), "date", []), "html", null, true);
                echo ": ";
                echo twig_escape_filter($this->env, twig_date_format_filter($this->env, twig_get_attribute($this->env, $this->source, $context["comment"], "comment_date", []), twig_get_attribute($this->env, $this->source, ($context["l10n"] ?? null), "date_format", [])), "html", null, true);
                echo "</dd>
        <dd>";
                // line 10
                echo twig_get_attribute($this->env, $this->source, $context["comment"], "comment_content", []);
                echo "</dd>
    ";
            }
        }
        $_parent = $context['_parent'];
        unset($context['_seq'], $context['_iterated'], $context['_key'], $context['comment'], $context['_parent'], $context['loop']);
        $context = array_intersect_key($context, $_parent) + $_parent;
        // line 13
        echo "</dl>";
    }

    public function getTemplateName()
    {
        return "comments.twig";
    }

    public function isTraitable()
    {
        return false;
    }

    public function getDebugInfo()
    {
        return array (  70 => 13,  61 => 10,  55 => 9,  49 => 8,  43 => 7,  38 => 6,  35 => 5,  31 => 4,  26 => 2,  23 => 1,);
    }

    public function getSourceContext()
    {
        return new Twig_Source("", "comments.twig", "/app/public/wp-content/plugins/wxr2pdf/templates/twig/comments.twig");
    }
}
