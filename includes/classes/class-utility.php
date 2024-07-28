<?php

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

/*
 * Utilities class
 *
 * @since 2.0
 */

class Alarnd_Utility
{

    /**
     * Constructor
     *
     */
    public function __construct()
    {
        add_action('admin_menu', array($this, 'add_translation_options_page'));
        add_action('admin_init', array($this, 'initialize_translation_options'));
        add_action('admin_enqueue_scripts', array($this, 'enqueue_admin_scripts'));
        add_action('wp_enqueue_scripts', array($this, 'enqueue_translation_script'));
    }

    public function add_translation_options_page()
    {
        add_menu_page(
            'Translations',
            'Translations',
            'manage_options',
            'translation-options',
            array($this, 'render_translation_options_page')
        );
    }

    public function render_translation_options_page()
    {
        ?>
        <div class="wrap">
            <h1><?php _e('Translations', 'your-textdomain'); ?></h1>
            <form method="post" action="options.php">
                <?php
                settings_fields('translation_options_group');
                do_settings_sections('translation-options');
                submit_button();
                ?>
            </form>
        </div>
        <?php
    }

    public function initialize_translation_options()
    {
        add_settings_section(
            'translation_section',
            'Translation Settings',
            array($this, 'render_translation_section'),
            'translation-options'
        );

        add_settings_field(
            'translations',
            'Translations',
            array($this, 'render_translations_fields'),
            'translation-options',
            'translation_section'
        );

        register_setting('translation_options_group', 'translations', array($this, 'sanitize_translations'));
    }

    public function render_translation_section()
    {
        echo '<p>Enter the translations below:</p>';
    }

    public function render_translations_fields()
    {
        $translations = get_option('translations', array());
        ?>
        <div id="translations-wrapper">
            <?php if (empty($translations)): ?>
                <div class="translation-pair">
                    <input type="text" name="translations[hebrew][]" placeholder="Hebrew Text">
                    <input type="text" name="translations[english][]" placeholder="English Translation">
                </div>
            <?php else: ?>
                <?php foreach ($translations['hebrew'] as $index => $hebrew): ?>
                    <div class="translation-pair">
                        <input type="text" name="translations[hebrew][]" value="<?php echo esc_attr($hebrew); ?>"
                            placeholder="Hebrew Text">
                        <input type="text" name="translations[english][]"
                            value="<?php echo esc_attr($translations['english'][$index]); ?>" placeholder="English Translation">
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
        <button type="button" id="add-translation"><?php _e('Add New Translation', 'your-textdomain'); ?></button>
        <?php
    }

    public function sanitize_translations($input)
    {
        $output = array(
            'hebrew' => array(),
            'english' => array()
        );

        if (isset($input['hebrew']) && is_array($input['hebrew'])) {
            foreach ($input['hebrew'] as $hebrew) {
                $output['hebrew'][] = sanitize_text_field($hebrew);
            }
        }

        if (isset($input['english']) && is_array($input['english'])) {
            foreach ($input['english'] as $english) {
                $output['english'][] = sanitize_text_field($english);
            }
        }

        return $output;
    }

    public function enqueue_admin_scripts($hook)
    {
        if ($hook !== 'toplevel_page_translation-options') {
            return;
        }

        wp_enqueue_script('translation-admin-script', get_template_directory_uri() . '/assets/js/translation-admin.js', array('jquery'), null, true);
    }

    public function enqueue_translation_script()
    {
        if ($this->is_current_user_contributor()) {
            wp_enqueue_script('translation-script', get_template_directory_uri() . '/assets/js/translation.js', array('jquery'), null, true);

            $translations = get_option('translations', array('hebrew' => array(), 'english' => array()));
            wp_localize_script('translation-script', 'translationData', $translations);
        }
    }

    private function is_current_user_contributor()
    {
        $current_user = wp_get_current_user();
        return in_array('contributor', (array) $current_user->roles);
    }

}

// Initialize the class
new Alarnd_Utility();
