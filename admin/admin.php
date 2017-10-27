<?php

class CPPS_Admin
{
    private $option_name = CPPS_Config::OPTION_NAME;
    private $option_group = CPPS_Config::OPTION_GROUP_NAME;
    private $option;

    public function __construct()
    {
        register_setting($this->option_group, $this->option_name, array($this, 'validation_sanitize'));
    }


    /**
     * Create admin page.
     */
    public function create()
    {
        wp_enqueue_style(CPPS_Config::NAME . '-admin', plugins_url(CPPS_Config::NAME . '/admin/css/style.css'), array(), CPPS_Config::version(), 'all');
        wp_enqueue_script(array('jquery', 'postbox'));

        add_meta_box('cpps_settings', __('Settings', CPPS_Config::DOMAIN), array($this, 'render_settings'), CPPS_Config::NAME, 'settings');

        $this->option = CPPS_Option::get();

        $this->render();
    }


    /**
     * @param $input
     * @return array|null
     */
    public function validation_sanitize($input)
    {
        $def_options = CPPS_Option::def();
        $old_options = CPPS_Option::get();

        $new_input = array();
        $new_input['delimiter'] = (isset($input['delimiter'])) ? $this->sanitize_delimiter(sanitize_text_field($input['delimiter'])) : $def_options['delimiter'];
        $new_input['use_pages'] = (isset($input['use_pages'])) ? $this->sanitize_checkbox($input['use_pages']) : 0;
        $new_input['use_single'] = (isset($input['use_single'])) ? $this->sanitize_checkbox($input['use_single']) : 0;

        if (strlen($new_input['delimiter']) === 0) {
            add_settings_error(
                $this->option_name,
                $this->option_name . '_validation_error',
                __('The entered delimiter can not be used.', CPPS_Config::DOMAIN),
                'error'
            );
            return $old_options;
        }

        if (!is_numeric($new_input['use_pages'])) {
            return $old_options;
        }

        if (!is_numeric($new_input['use_single'])) {
            return $old_options;
        }

        return $new_input;
    }


    /**
     * @param $input
     * @return int
     */
    private function sanitize_checkbox($input)
    {
        $input = (int)$input;

        return ($input === 1) ? 1 : 0;
    }

    /**
     * Reference wp-includes/formatting.php: sanitize_title_with_dashes function.
     *
     * @param $title
     * @param string $raw_title
     * @param string $context
     *
     * @return mixed|string
     */
    private function sanitize_delimiter( $title) {
        $title = preg_replace('/[^A-Za-z0-9\.\/_-]/', '', $title);
        return $title;
    }


    /**
     * Get 'Page' rules.
     * Reference wp-includes/template.php: get_page_template function comments.
     *
     * @return array
     */
    private function page_template_load_rules()
    {
        $delimiter = sanitize_text_field($this->option['delimiter']);
        $id = '{current_id}';
        $slug = '{current_slug}';

        $templates = array();
        $templates[] = "page-{$slug}.php";
        $templates[] = "page-{$id}.php";
        $templates[] = 'page.php';

        $ids = array('{parent_id}', $id);
        if (!empty($ids)) {
            array_unshift($templates, 'page-' . implode($delimiter, $ids) . '.php');
        }

        $slugs = array('{parent_slug}', $slug);
        if (!empty($slugs)) {
            array_unshift($templates, 'page-' . implode($delimiter, $slugs) . '.php');
        }

        //array_unshift($templates, '{Page Template}.php');

        return $templates;
    }


    /**
     * Get 'Single' rules.
     * Reference wp-includes/template.php: get_single_template function comments.
     *
     * @return array
     */
    private function single_template_load_rules()
    {
        $delimiter = sanitize_text_field($this->option['delimiter']);
        $post_type = '{post_type}';
        $slug = '{current_slug}';

        $templates = array();
        $templates[] = "single-{$post_type}-{$slug}.php";
        $templates[] = "single-{$post_type}.php";

        $slugs = array('{parent_slug}', $slug);
        if (!empty($slugs)) {
            array_unshift($templates, "single-{$post_type}-" . implode($delimiter, $slugs) . '.php');
        }

        //array_unshift($templates, '{Post Type Template}.php');

        return $templates;
    }


    /**
     * render settings
     */
    public function render_settings()
    {
        $delimiter = $this->option['delimiter'];

        ?>
        <table class="form-table">
            <tbody>
            <tr>
                <th><label for="CPPS_Delimiter"><?php echo __('Delimiter', CPPS_Config::DOMAIN); ?></label></th>
                <td>
                    <input type="text" class="input-text-delimiter" id="CPPS_Delimiter" name="<?php echo $this->option_name; ?>[delimiter]" value="<?php echo esc_attr($delimiter); ?>">
                    <p class="note"><?php echo __('The following characters are allowed:', CPPS_Config::DOMAIN) ?> A-Z a-z 0-9 . _ - /</p>
                </td>
            </tr>

            <tr>
                <th><?php echo __('Pages', CPPS_Config::DOMAIN); ?></th>
                <td>
                    <label>
                        <input type="checkbox" name="<?php echo $this->option_name; ?>[use_pages]" value="1" <?php checked($this->option['use_pages'], 1); ?> />
                        <?php echo __('use', CPPS_Config::DOMAIN); ?>
                    </label>

                    <div class="cpps-text-section">
                        <p class="title"><?php echo __('Rules to be added.', CPPS_Config::DOMAIN); ?></p>
                        <p>page-{parent_slug}<?php echo esc_html($delimiter); ?>{current_slug}.php</p>
                        <p>page-{parent_post_id}<?php echo esc_html($delimiter); ?>{current_post_id}.php</p>
                    </div>

                    <div class="cpps-text-section">
                        <p class="title"><?php echo __('Priority of template loading.', CPPS_Config::DOMAIN); ?></p>
                        <ul>
                            <?php
                            $rules = $this->page_template_load_rules();
                            foreach ($rules as $index => $rule) : ?>
                                <li><span><?php echo $index+1; ?>:&nbsp;&nbsp;</span><?php echo esc_html($rule); ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                </td>
            </tr>

            <tr>
                <th><?php echo __('Single', CPPS_Config::DOMAIN); ?></th>
                <td>
                    <label>
                        <input type="checkbox" name="<?php echo $this->option_name; ?>[use_single]" value="1" <?php checked($this->option['use_single'], 1); ?> />
                        <?php echo __('use', CPPS_Config::DOMAIN); ?>
                    </label>
                    <p class="note"><?php echo __('* Can be used if hierarchy setting is enabled for \'Custom Post Types\'.', CPPS_Config::DOMAIN); ?></p>

                    <div class="cpps-text-section">
                        <p class="title"><?php echo __('Rules to be added.', CPPS_Config::DOMAIN); ?></p>
                        <p>single-{post_type}-{parent_slug}<?php echo esc_html($delimiter); ?>{current_slug}.php</p>
                    </div>

                    <div class="cpps-text-section">
                        <p class="title"><?php echo __('Priority of template loading.', CPPS_Config::DOMAIN); ?></p>
                        <ul>
                            <?php
                            $rules = $this->single_template_load_rules();
                            foreach ($rules as $index => $rule) : ?>
                                <li><span><?php echo $index+1; ?>:&nbsp;&nbsp;</span><?php echo esc_html($rule); ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                </td>
            </tr>
            </tbody>
        </table>

        <?php submit_button(); ?>
        <?php
    }


    /**
     * render messages
     *
     * @param $_messages
     * @param $state
     */
    private function render_messages($_messages, $state)
    {
        ?>
        <div class="<?php echo $state; ?>">
            <ul>
                <?php foreach ($_messages as $message): ?>
                    <li><?php echo esc_html($message); ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
        <?php
    }


    /**
     * render
     */
    private function render()
    {
        ?>
        <script type="text/javascript">
          jQuery(function ($) {
            postboxes.add_postbox_toggles('<?php echo CPPS_Config::NAME; ?>');
            $('.if-js-closed').removeClass('if-js-closed').addClass('closed');
          });
        </script>

        <div class="wrap cpps-wrap">
            <h2>Concat Parent Page Slugs</h2>
            <?php
            if ($messages = get_transient('post-updated')) {
                $this->render_messages($messages, 'updated');

            } elseif ($messages = get_transient('post-error')) {
                $this->render_messages($messages, 'error');

            }
            ?>
            <form action="options.php" method="post" enctype="multipart/form-data">
                <div class="metabox-holder">
                    <?php
                    wp_nonce_field('closedpostboxes', 'closedpostboxesnonce', false);
                    wp_nonce_field('meta-box-order', 'meta-box-order-nonce', false);

                    settings_fields($this->option_group);
                    do_meta_boxes(CPPS_Config::NAME, 'settings', null);
                    ?>
                </div>
            </form>
        </div>

        <?php
    }
}



