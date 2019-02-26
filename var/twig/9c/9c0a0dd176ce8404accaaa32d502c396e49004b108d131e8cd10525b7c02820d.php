<?php

/* page.twig */
class __TwigTemplate_c6a77bbd7920290cfbc912c058aeaf2df933c18d1c3631e11a6326a58eae8eaa extends Twig_Template
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
        $context['_parent'] = $context;
        $context['_seq'] = twig_ensure_traversable(($context["posts"] ?? null));
        $context['loop'] = [
          'parent' => $context['_parent'],
          'index0' => 0,
          'index'  => 1,
          'first'  => true,
        ];
        if (is_array($context['_seq']) || (is_object($context['_seq']) && $context['_seq'] instanceof Countable)) {
            $length = count($context['_seq']);
            $context['loop']['revindex0'] = $length - 1;
            $context['loop']['revindex'] = $length;
            $context['loop']['length'] = $length;
            $context['loop']['last'] = 1 === $length;
        }
        foreach ($context['_seq'] as $context["_key"] => $context["post"]) {
            // line 2
            echo "    <a name=\"";
            echo twig_escape_filter($this->env, twig_get_attribute($this->env, $this->source, $context["post"], "post_id", []), "html", null, true);
            echo "\" id=\"";
            echo twig_escape_filter($this->env, twig_get_attribute($this->env, $this->source, $context["post"], "post_id", []), "html", null, true);
            echo "\"></a>
    ";
            // line 3
            if ((twig_length_filter($this->env, twig_get_attribute($this->env, $this->source, $context["post"], "featured_image", [])) > 0)) {
                // line 4
                echo "        <img class=\"aligncenter\" src=\"";
                echo twig_escape_filter($this->env, twig_get_attribute($this->env, $this->source, $context["post"], "featured_image", []), "html", null, true);
                echo "\" />
    ";
            }
            // line 6
            echo "    <h1 class=\"entry-title\">";
            echo twig_escape_filter($this->env, twig_get_attribute($this->env, $this->source, $context["post"], "post_title", []), "html", null, true);
            echo "</h1>
    ";
            // line 7
            echo twig_escape_filter($this->env, twig_get_attribute($this->env, $this->source, ($context["l10n"] ?? null), "by", []), "html", null, true);
            echo " ";
            echo twig_escape_filter($this->env, twig_get_attribute($this->env, $this->source, $context["post"], "author_display_name", []), "html", null, true);
            echo " (";
            echo twig_escape_filter($this->env, twig_get_attribute($this->env, $this->source, $context["post"], "author_email", []), "html", null, true);
            echo ") - ";
            echo twig_escape_filter($this->env, twig_get_attribute($this->env, $this->source, ($context["l10n"] ?? null), "date", []), "html", null, true);
            echo ": ";
            echo twig_escape_filter($this->env, twig_date_format_filter($this->env, twig_get_attribute($this->env, $this->source, $context["post"], "post_date", []), twig_get_attribute($this->env, $this->source, ($context["l10n"] ?? null), "date_format", [])), "html", null, true);
            echo "
    <hr />
    ";
            // line 9
            if ((twig_length_filter($this->env, twig_get_attribute($this->env, $this->source, $context["post"], "post_excerpt", [])) > 0)) {
                // line 10
                echo "        <p><em>";
                echo twig_escape_filter($this->env, twig_get_attribute($this->env, $this->source, $context["post"], "post_excerpt", []));
                echo "</em></p>
    ";
            }
            // line 12
            echo "    ";
            echo twig_get_attribute($this->env, $this->source, $context["post"], "post_content", []);
            echo "

    ";
            // line 14
            if ((twig_length_filter($this->env, twig_get_attribute($this->env, $this->source, $context["post"], "comments", [])) > 0)) {
                // line 15
                echo "        ";
                $this->loadTemplate("comments.twig", "page.twig", 15)->display($context);
                // line 16
                echo "    ";
            }
            // line 17
            echo "
    ";
            // line 18
            if ( !twig_get_attribute($this->env, $this->source, $context["loop"], "last", [])) {
                // line 19
                echo "        <pagebreak />
    ";
            }
            ++$context['loop']['index0'];
            ++$context['loop']['index'];
            $context['loop']['first'] = false;
            if (isset($context['loop']['length'])) {
                --$context['loop']['revindex0'];
                --$context['loop']['revindex'];
                $context['loop']['last'] = 0 === $context['loop']['revindex0'];
            }
        }
        $_parent = $context['_parent'];
        unset($context['_seq'], $context['_iterated'], $context['_key'], $context['post'], $context['_parent'], $context['loop']);
        $context = array_intersect_key($context, $_parent) + $_parent;
    }

    public function getTemplateName()
    {
        return "page.twig";
    }

    public function isTraitable()
    {
        return false;
    }

    public function getDebugInfo()
    {
        return array (  100 => 19,  98 => 18,  95 => 17,  92 => 16,  89 => 15,  87 => 14,  81 => 12,  75 => 10,  73 => 9,  60 => 7,  55 => 6,  49 => 4,  47 => 3,  40 => 2,  23 => 1,);
    }

    public function getSourceContext()
    {
        return new Twig_Source("", "page.twig", "/app/public/wp-content/plugins/wxr2pdf/templates/twig/page.twig");
    }
}
