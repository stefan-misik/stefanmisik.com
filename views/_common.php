<?php

/**
 * @brief Print first part of common HTML header
 *
 * @param string $additional_title Additional title string
 */
function pagePreHeader(
        $additional_title = NULL
        )
{
?><!DOCTYPE html>
<html lang="en">
    <head>
        <title><?php echo ((NULL != $additional_title) ? $additional_title . " - " : "" ) . getPageTitle(); ?></title>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <!-- Favicons -->
        <link rel="apple-touch-icon-precomposed" sizes="57x57" href="<?php echo getUrlAddress("public/images/icon/apple-touch-icon-57x57.png"); ?>" />
        <link rel="apple-touch-icon-precomposed" sizes="114x114" href="<?php echo getUrlAddress("public/images/icon/apple-touch-icon-114x114.png"); ?>" />
        <link rel="apple-touch-icon-precomposed" sizes="72x72" href="<?php echo getUrlAddress("public/images/icon/apple-touch-icon-72x72.png"); ?>" />
        <link rel="apple-touch-icon-precomposed" sizes="144x144" href="<?php echo getUrlAddress("public/images/icon/apple-touch-icon-144x144.png"); ?>" />
        <link rel="apple-touch-icon-precomposed" sizes="60x60" href="<?php echo getUrlAddress("public/images/icon/apple-touch-icon-60x60.png"); ?>" />
        <link rel="apple-touch-icon-precomposed" sizes="120x120" href="<?php echo getUrlAddress("public/images/icon/apple-touch-icon-120x120.png"); ?>" />
        <link rel="apple-touch-icon-precomposed" sizes="76x76" href="<?php echo getUrlAddress("public/images/icon/apple-touch-icon-76x76.png"); ?>" />
        <link rel="apple-touch-icon-precomposed" sizes="152x152" href="<?php echo getUrlAddress("public/images/icon/apple-touch-icon-152x152.png"); ?>" />
        <link rel="icon" type="image/png" href="<?php echo getUrlAddress("public/images/icon/favicon-196x196.png"); ?>" sizes="196x196" />
        <link rel="icon" type="image/png" href="<?php echo getUrlAddress("public/images/icon/favicon-96x96.png"); ?>" sizes="96x96" />
        <link rel="icon" type="image/png" href="<?php echo getUrlAddress("public/images/icon/favicon-32x32.png"); ?>" sizes="32x32" />
        <link rel="icon" type="image/png" href="<?php echo getUrlAddress("public/images/icon/favicon-16x16.png"); ?>" sizes="16x16" />
        <link rel="icon" type="image/png" href="<?php echo getUrlAddress("public/images/icon/favicon-128.png"); ?>" sizes="128x128" />
        <meta name="application-name" content="Stefanmisik.com"/>
        <meta name="msapplication-TileColor" content="#FFFFFF" />
        <meta name="msapplication-TileImage" content="<?php echo getUrlAddress("public/images/icon/mstile-144x144.png"); ?>" />
        <meta name="msapplication-square70x70logo" content="<?php echo getUrlAddress("public/images/icon/mstile-70x70.png"); ?>" />
        <meta name="msapplication-square150x150logo" content="<?php echo getUrlAddress("public/images/icon/mstile-150x150.png"); ?>" />
        <meta name="msapplication-wide310x150logo" content="<?php echo getUrlAddress("public/images/icon/mstile-310x150.png"); ?>" />
        <meta name="msapplication-square310x310logo" content="<?php echo getUrlAddress("public/images/icon/mstile-310x310.png"); ?>" />
        <!-- Styles -->
        <link rel="stylesheet" type="text/css" href="<?php echo getUrlAddress("public/stylesheets/main.css"); ?>">

        <!-- KaTex -->
        <link rel="stylesheet" type="text/css" href="<?php echo getUrlAddress("public/katex/katex.min.css"); ?>">
        <script src="<?php echo getUrlAddress("public/katex/katex.min.js"); ?>"></script>

        <!-- Code highlighter -->
        <link rel="stylesheet" href="<?php echo getUrlAddress("public/codehighlight/codehighlight.css"); ?>">
        <script src="<?php echo getUrlAddress("public/codehighlight/highlight.pack.js"); ?>"></script>
        <script>
            // Do not auto-detect languages
            hljs.configure({languages: []});
            // Attach code highlighter to the on load event
            hljs.initHighlightingOnLoad();
        </script>
<?php }


/**
 * @brief Print second part of the header
 *
 */
function pagePostHeader(
        )
{
?>
    </head>
    <body>
        <!-- Page header -->
        <header class="page-section page-header">
            <?php if (!isTextBased()) { ?>
            <a href="<?php echo getUrlAddress(""); ?>">
                <object class="page-logo" style="background-image: url('<?php echo getUrlAddress("public/images/logo.png"); ?>')" data="<?php echo getUrlAddress("public/images/logo.svg"); ?>" type="image/svg+xml"><?php echo getPageTitle(); ?></object>
            </a>
            <?php } else { ?>
            <pre>
<?php readfile("public/logo.txt"); ?>
            <a href="<?php echo getUrlAddress(""); ?>">Stefan Misik</a>
            </pre>
            <?php } ?>
        </header>

        <hr class="page-section-divider">

        <!-- Page content -->
        <section class="page-section page-content">
<?php }

/**
 * @brief Print common HTML footer
 */
function pageFooter(
        )
{
?>
        </section>

        <hr class="page-section-divider">

        <!-- Page footer -->
        <footer class="page-section page-footer">
            <aside>
                <nav>
                    <a href="<?php echo(getUrlAddress("rss.xml")); ?>">RSS</a> &bull;
                    <a href="https://www.linkedin.com/in/stefan-misik">Linkedin</a> &bull;
                    <a href="https://github.com/stefan-misik">GitHub</a>
                </nav>
                &copy; <?php echo date("Y"); ?> Stefan Misik
            </aside>
        </footer>
        <script>
            function processEquationText(text)
            {
                return text.replace(/&([#0-9A-Za-z]+);/g,
                    function (match, entity, offset, str)
                {
                    switch (entity.toLowerCase())
                    {
                        case '#38':
                        case 'amp': return "&";
                        case '#60':
                        case 'lt': return "<";
                        case '#62':
                        case 'gt': return ">";
                        default: return "";
                    }
                });
            }

            equation_tags = document.getElementsByClassName('equation');
            Array.prototype.forEach.call(equation_tags, function (tag)
            {
                eq_str = processEquationText(tag.innerHTML);
                tag.innerHTML = "";

                switch (tag.tagName)
                {
                case 'SPAN':
                    katex.render(eq_str, tag, {throwOnError: false});
                    break;
                case 'P':
                    katex.render(eq_str, tag, {throwOnError: false, displayMode: true});
                    break;
                }
            });
        </script>

    </body>
</html><?php
}
