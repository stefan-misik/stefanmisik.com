<?php pagePreHeader(); ?>
<?php pagePostHeader(); ?>
            <header>
                <h1>Archive</h1>
            </header>
            <?php
            $old_group = NULL; 
            while($post_record = $logic->getNextPost())
            {
                $post = new Post($post_record);
                $group = formatArchiveGroupTime($post->getUpdateTime());
                if($group != $old_group)
                {
                    if(NULL != $old_group)
                    {
                        echo "</ul></nav></section>";
                    }
            ?>
                <section>
                    <header><h2 class="nav-title"><?php echo $group; ?></h2></header>
                    <nav><ul>
                <?php 
                }
                ?>
                        <li><a href="<?php echo $post->getLink(); ?>"><?php echo $post->getTitle(); ?></a></li>
            <?php
                $old_group = $group;
            }
            
            if(NULL != $old_group)
            {
                echo "</ul></nav></section>";
            }
            ?>
                        
<?php
pageFooter();
