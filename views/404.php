<?php pagePreHeader("Page not found"); ?>
<?php pagePostHeader(); ?>
            <article>
                <header>
                    <h1>Sorry, something has gone wrong</h1>
                </header>
				<p>
					You can try to go to the <a href="<?php echo getUrlAddress(""); ?>"> home page</a>.
				</p>
            </article>
<?php
pageFooter();
