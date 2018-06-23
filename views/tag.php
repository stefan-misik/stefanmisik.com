<?php pagePreHeader(); ?>
<?php pagePostHeader(); ?>
            <header>
                <h1>#<?php echo($logic["tag"]); ?></h1>
                <aside>
                    The tag has <?php echo($logic["source"]->countResults()); ?> <?php echo(usePlural($logic["source"]->countResults(), "post")); ?>
                </aside>
            </header>    
            <nav>
                <ul>
                <?php
                while($post_record = $logic["source"]->getNextPost())
                {
                    $post = new Post($post_record);
                ?>
                    <li>
                        <a href="<?php echo $post->getLink(); ?>">
                            <?php echo $post->getTitle(); ?>
                        </a>
                    </li>
                <?php } ?>
                </ul>
            </nav>
            <hr>
            <footer>
                <nav>
                    <ul>
                        <li><a href="<?php echo getUrlAddress("archive"); ?>"><strong>List all posts</strong></a></li>
                    </ul>
                </nav>
            </footer>

<?php
pageFooter();