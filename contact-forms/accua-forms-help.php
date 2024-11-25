<?php
    class AccuaFormsHelp {

        private $helpTextSet;
        private $counter;

        function __construct() {
            $this->helpTextSet = array();
            $this->counter = 0;
        }

        public static function getInstance() {
            static $instance = null;
            if ($instance === null) {
                $instance = new AccuaFormsHelp();
            }
            return $instance;
        }

        private function _accua_forms_get_help_text($name) {
            $ret = '';
            switch ($name) {
                /*
                case 'help_1':
                    $ret = array(
                        'header' => __('1', 'contact-forms'),
                        'text' => __('1', 'contact-forms')
                    );
                    break;
                case 'help_2':
                    $ret = array(
                        'header' => __('2', 'contact-forms'),
                        'text' => __('2', 'contact-forms').
                            "<br/><dl>".
                            "<dt>aaaa</dt><dd>". __('bbb', 'contact-forms')."</dd>".
                            "<dt>cccc</dt><dd>". __('dddd', 'contact-forms')."</dd>".
                            "</dl>",
                    );
                    break;
                */
                case 'form_edit_preview':
                    $ret = array(
                         'header' => __('Unstyled Preview', 'contact-forms'),
                         'text' => __('This representation does not incorporate the active theme styles of your website. To view the finalized look, integrate this form within your website.', 'contact-forms'),
                    );
                    break;
                case 'contact_forms_lead_statuses':
                    $ret = array(
                        'header' => __('Lead statuses', 'contact-forms'),
                        'text' => __('<strong>Spam</strong> - All submissions that can be discarded immediately, including submission tests<br />
                            <strong>Job Candidate</strong> - Includes spontaneous and specific job applications<br />
                            <strong>Lead</strong> - Unclear (general info request)<br />
                            <strong>Prospect</strong> - A qualified lead passed to Sales<br />
                            <strong>Opportunity</strong> - Quote / Pricing request that must be followed up<br />
                            <strong>Customer</strong> - Has already purchased<br />
                            <strong>Supplier</strong> - Contact whose role is or can only be supplier of goods and services<br />
                            <strong>Other</strong> - Contact is valid but not within lead generation', 'contact-forms'),
                    );
                    break;
                default:
                    break;
            }
            return $ret;
        }

        /*
         * Questa funzione va chiamata dalle classi che instanziano questo un oggetto AccuaFormsHelp
         * per ottenere l'oggetto HTML che conterrà il pulsante di help
         */
        function add_pointer($pointerName) {
            // $version = '1_0'; // replace all periods in 1.0 with an underscore
            // $prefix = 'custom_admin_pointers' . $version . '_';
            $pointer_content = '';
            $htmlContainer = '';
            $res = $this->_accua_forms_get_help_text($pointerName);
            if (is_Array($res)) {
                if (isset($res['header'])) {
                    $pointer_content .= '<h3>' . $res['header']. '</h3>';
                }
                if (isset($res['text'])) {
                    $pointer_content .= '<p>' . $res['text'] . '</p>';
                }

                $id = $this->counter++;
                //$id = count($this->helpTextSet);
                $anchor_id = "accua_forms_tooltip_$id";

                $this->helpTextSet[] = array(
                    'content' => $pointer_content,
                    'anchor_id' => "#" . $anchor_id,
                    'edge' => 'left',
                    'align' => 'left'
                );

                $htmlContainer = "<span id='$anchor_id' class='tooltip dashicons dashicons-editor-help'></span>";
            }
            return $htmlContainer;
        }

        /*
         * Questa funzione va chiamata alla fine per mettere in output il javascript che
         * gestisce la generazione + comparsa/scomparsa del Tooltip.
         * Andrebbe chiamata una sola volta
         */
        function finished() {
            if ($this->helpTextSet) {
            ?>
            <script type="text/javascript">
                /* <![CDATA[ */
                jQuery(document).ready(function($) {
                    <?php
                    foreach ($this->helpTextSet as $pointer) {
                    // error_log(print_r($pointer, true));
                    ?>
                    $(<?php echo _accua_forms_json_encode($pointer['anchor_id']); ?>).click(function(){
                        $( <?php echo _accua_forms_json_encode($pointer['anchor_id']); ?> ).pointer( {
                            content: <?php echo _accua_forms_json_encode($pointer['content']); ?>,
                            position: {
                                edge: <?php echo _accua_forms_json_encode($pointer['edge']); ?>,
                                align: <?php echo _accua_forms_json_encode($pointer['align']); ?>
                            },
                        }).pointer( 'open' );
                        $("a.close").html("&nbsp;");
                        //$("a.close").addClass("notice-dismiss");
                    });
                    <?php
                    }
                    ?>
                });
                /* ]]> */
            </script>
            <?php
            }
        }
    }
