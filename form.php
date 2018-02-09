<div class="wrap">
    <h1>Statamic JSON Export</h1>

    <p>When you click the button below the plugin will export a JSON file that you can import to your Statamic application.</p>

    <p>Once you’ve saved the download file, you can enjoy the flat file goodness.</p>

    <h2>Choose what to export</h2>

    <form method="POST" action="<?php echo get_site_url(); ?>/wp-content/plugins/wordpress-exporter/export.php">

        <fieldset>
            <input type="hidden" name="pathname" value="<?php echo get_home_path(); ?>">

            <p>
                <label><input type="checkbox" name="post" value="post">Post</label>
            </p>

            <p>
                <label><input type="checkbox" name="page" value="page">Page</label>
            </p>

            <p>
                <label><input type="checkbox" name="settings" value="settings">Settings</label>
            </p>

            <p>
                <label><input type="checkbox" name="taxonomies" value="taxonomies">Taxonomies</label>
            </p>
        </fieldset>


        <?php submit_button( 'Download JSON File' ); ?>

    </form>
</div>
