<?php echo("<?xml version=\"1.0\" ?>\n"); ?>
<rss version="2.0" xmlns:atom="http://www.w3.org/2005/Atom">
<channel>
    <title><?php echo getPageTitle(); ?></title>
    <link><?php echo getUrlAddress(""); ?></link>
    <description>
        My personal technical blog concerned with programming and embedded
        systems.
    </description>
    <atom:link href="<?php echo(getUrlAddress("rss.xml")); ?>" rel="self" type="application/rss+xml" />
<?php
        while ($post_record = $logic->getNextPost())
        {
            $post = new Post($post_record);
?>
        <item>
            <title><?php echo($post->getTitle()); ?></title>
            <link><?php echo($post->getLink()); ?></link>
            <pubDate><?php echo(date(DATE_RFC2822, $post->getPublishTime())); ?></pubDate>
            <guid isPermaLink="false"><?php echo($post->getSlug() . "," . $post->getPublishTime()); ?></guid>
            <description>
                <?php echo($post->getExcerpt() . "\n"); ?>
            </description>
        </item>

<?php
        }
?>
</channel>
</rss>
