<div class="wrap">
    <h1>Export to Statamic</h1>

    <p>Choose what data you would like to export to a Statamic JSON import file.</p>

    <p>Learn more about <a href="https://docs.statamic.com/importer" target="_blank">Statamic's Importer</a>.</p>

    <h2>Customize your export</h2>

    <form method="POST" action="<?php echo admin_url( 'admin-post.php' ); ?>">
        <fieldset>
            <input type="hidden" name="action" value="statamic_export_run">

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
