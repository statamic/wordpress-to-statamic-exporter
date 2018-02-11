<div class="wrap">
    <h1>Statamic JSON Export</h1>

    <p>When you click the button below the plugin will export a JSON file that you can import to your Statamic application.</p>

    <p>Once you’ve saved the download file, you can enjoy the flat file goodness.</p>

    <h2>Choose what to export</h2>

    <form method="POST">
        <fieldset>
            <input type="hidden" name="pathname" value="<?php echo get_home_path(); ?>">

            <p>
                <label><input type="checkbox" name="content[]" value="posts">Posts</label>
            </p>

            <p>
                <label><input type="checkbox" name="content[]" value="pages">Pages</label>
            </p>

            <p>
                <label><input type="checkbox" name="content[]" value="settings">Settings</label>
            </p>

            <p>
                <label><input type="checkbox" name="content[]" value="taxonomies">Taxonomies</label>
            </p>

            <?php foreach($postTypes as $postType): ?>
                <p>
                    <label>
                        <input type="checkbox" name="post_types[]" value="<?php echo $postType->name; ?>">
                        <?php echo $postType->label; ?>
                    </label>
                </p>
            <?php endforeach; ?>
        </fieldset>


        <?php submit_button( 'Download JSON File' ); ?>

    </form>
</div>
