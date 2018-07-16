<div class="wrap" id="clients-wp-merge-wrap">
    <h1>Clients WP - FreshBooks</h1>
    <br />
    <?php settings_errors() ?>
    <div class="content-wrap">
        <?php
            $cwpfreshbooks_settings_options = get_option('cwpfreshbooks_settings_options');
            $app_domain = isset($cwpfreshbooks_settings_options['app_domain']) ? $cwpfreshbooks_settings_options['app_domain'] : '';
            $app_token = isset($cwpfreshbooks_settings_options['app_token']) ? $cwpfreshbooks_settings_options['app_token'] : '';
            $clients_list = isset($cwpfreshbooks_settings_options['clients_list']) ? $cwpfreshbooks_settings_options['clients_list'] : '';
        ?>
        <br />
        <form method="post" action="options.php">
            <?php settings_fields( 'cwpfreshbooks_settings_options' ); ?>
            <?php do_settings_sections( 'cwpfreshbooks_settings_options' ); ?> 
            <table class="form-table">
                <tbody>
                    <tr class="form-field form-required term-name-wrap">
                        <th scope="row">
                            <label>App Domain</label>
                        </th>
                        <td>
                            <input type="text" name="cwpfreshbooks_settings_options[app_domain]" size="40" width="40" value="<?= $app_domain ?>" placeholder="https://{domain}.freshbooks.com">
                        </td>
                    </tr>
                    <tr class="form-field form-required term-name-wrap">
                        <th scope="row">
                            <label>Authorization Token</label>
                        </th>
                        <td>
                            <input type="text" name="cwpfreshbooks_settings_options[app_token]" size="40" width="40" value="<?= $app_token ?>">
                        </td>
                    </tr>
                    <?php if (!empty($app_domain) && !empty($app_token)) { ?>
                        <tr class="form-field form-required term-name-wrap">
                            <th scope="row">
                                <label>FreshBooks Clients</label>
                            </th>
                            <td>
                               <textarea rows="5" readonly="" name="cwpfreshbooks_settings_options[clients_list]"><?= $clients_list ?></textarea>
                            </td>
                        </tr>
                    <?php } ?>
                </tbody>
            </table>
            <p>
                <input type="submit" name="save_settings" class="button button-primary" value="Save">
                <?php if (!empty($app_domain) && !empty($app_token)): ?>
                    <a href="<?= admin_url( 'edit.php?post_type=bt_client&page=cwp-freshbooks&cwpintegration=freshbooks' ); ?>" class="button button-primary">Sync FreshBooks Clients</a>
                <?php endif; ?>
            </p>
        </form>
    </div>
</div>
