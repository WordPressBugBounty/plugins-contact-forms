<?php
if(!class_exists('WP_List_Table')){
    require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );
}

class Accua_Forms_Submissions_List_Table extends WP_List_Table {

    var $message = NULL;
    var $active_items = 0;
    var $del_items = 0;

    var $items_per_lead_status = array();

    public $export_xls = false;

    function __construct(){
        global $status, $page;
        $this->message = '';
        parent::__construct( array(
            'singular'  => 'submission',
            'plural'    => 'submissions',
            'ajax'      => false
        ) );
    }

    function get_num_of_active_items () {
      return $this->active_items;
    }

    function get_num_of_del_items () {
      return $this->del_items;
    }

    function get_items_per_lead_status() {
      return $this->items_per_lead_status;
    }

    function column_default($item, $column_name){
      if (isset($item[$column_name])) {
        //return htmlspecialchars($item[$column_name], ENT_QUOTES);
        return $item[$column_name];
      } else {
        return '';
      }
    }

    function column_cb($item){
        return sprintf(
            '<input type="checkbox" name="%1$s[]" value="%2$s" />',
            /*$1%s*/ $this->_args['singular'],
            /*$2%s*/ $item['ID']
        );
    }

    function column_lead_status($item) {
      return accua_forms_select_lead_status($item['ID'], $item['lead_status']);
    }

    function column_singlesub($item){
        return '<a href="?page=accua_forms_submissions_list&sid='.$item['ID'].'" class="">'
          .__("Review", 'contact-forms').'</a>';
    }

    function set_message($single_message) {
      $this->message=$single_message;
    }

    function get_message() {
      if($this->message!=NULL)
        return $this->message;
      else
        return NULL;
    }

    function get_columns(){
        global $wpdb;
        $columns = array(
            'cb' => '<input type="checkbox" />', //Render a checkbox instead of text
            'ID' => 'ID',
            'form_title' => 'Form',
            'form_id' => __("Form ID", 'contact-forms'),
            'pid' => __("Page ID", 'contact-forms'),
            'ip' => 'IP',
            'uri'  => __("Page", 'contact-forms'),
            'referrer' => __("Referrer", 'contact-forms'),
            'lang' => __("Language", 'contact-forms'),
            'created' => __("Opened", 'contact-forms'),
            'submitted' => __("Submitted", 'contact-forms'),
        );
        $query = "SELECT DISTINCT afsv_field_id FROM `{$wpdb->prefix}accua_forms_submissions_values`";
        $res = $wpdb->get_col($query);

        $avail_fields = get_option('accua_forms_avail_fields', array());
        foreach ($res as $col) {
          if (empty($avail_fields[$col])) {
            $columns['_field_'.$col] = $col . __("removed", 'contact-forms');
          } else {
            $columns['_field_'.$col] = $avail_fields[$col]['name'];
          }
        }
        if(isset($_GET['del']) && $_GET['del']==1){

        } else{
            $columns['lead_status'] = __("Lead Status", 'contact-forms');
	        $columns['singlesub'] = __("Review", 'contact-forms');
        }
        return $columns;
    }

    function get_bulk_actions() {
      if(isset($_GET['del']) && $_GET['del']==1) {
        $actions = array(
            'restore' => __('Restore', 'contact-forms'),
            'shred' => __('Permanently delete', 'contact-forms'),
        );
      } else {
        $actions = array(
            'delete' => __('Move to trash', 'contact-forms')
        );
      }
      return $actions;
    }

    function prepare_items($all=false, $get = array()) {
        global $wpdb, $hook_suffix;

        if (!$get) {
          $get = stripslashes_deep($_GET);
        }

        $per_page = 100;
        $del = isset($get['del']) && $get['del']==1;

        $this->active_items = $wpdb->get_var("SELECT COUNT(*) FROM `{$wpdb->prefix}accua_forms_submissions` WHERE afs_status >= 0");
        $this->del_items = $wpdb->get_var("SELECT COUNT(*) FROM `{$wpdb->prefix}accua_forms_submissions` WHERE afs_status < 0");

        $this->items_per_lead_status = $wpdb->get_results("SELECT afs_lead_status AS status, COUNT(*) AS n
                   FROM `{$wpdb->prefix}accua_forms_submissions`
                   WHERE afs_status >= 0
                   GROUP BY afs_lead_status", OBJECT_K);

        $filter = $filter_query_custom_field = "";
        $search = '';

        if(isset($get['_wp_http_referer'])) {
          foreach (explode('&', $get['_wp_http_referer']) as $coppia) {
            $param = explode("=", $coppia);
            if($param[0]=='fid' && $param[1]!='-1') $filter .= $wpdb->prepare(" AND afs_form_id = '%s' ", $param[1]);
            if($param[0]=='pid' && $param[1]!='-1') $filter .= $wpdb->prepare(" AND afs_post_id = '%d' ", $param[1]);
            if($param[0]=='year' && $param[1]>0) $filter .= $wpdb->prepare(" AND year(afs_submitted) = %d ", $param[1]);
            if($param[0]=='month' && $param[1]>0) $filter .= $wpdb->prepare(" AND month(afs_submitted) = %d ", $param[1]);
            if($param[0]=='s') $search = trim($param[1]);
          }
        }

        if(isset($get['fid']) && ($get['fid'])!=-1) {
          $filter .= $wpdb->prepare(" AND afs_form_id = '%s' ", $get['fid']);
        }
        if(isset($get['pid']) && ($get['pid']!=-1)) {
          $filter .= $wpdb->prepare(" AND afs_post_id = %d ", $get['pid']);
        }

        if(isset($get['year']) && ($get['year']>0)) {
          $filter .= $wpdb->prepare(" AND year(afs_submitted) = %d ", $get['year']);
        }
        if(isset($get['month']) && ($get['month']>0)) {
          $filter .= $wpdb->prepare(" AND month(afs_submitted) = %d ", $get['month']);
        }

        if(isset($get['s'])) {
            $search = trim($get['s']);
        }

        if (isset($get['date_from']) && $get['date_from'] !== '') {
          $filter .= $wpdb->prepare(" AND afs_submitted >= '%s' ", $get['date_from']);
        }
        if (isset($get['date_to']) && $get['date_to'] !== '') {
          $filter .= $wpdb->prepare(" AND afs_submitted < '%s' ", $get['date_to']);
        }


        $columns = $this->get_columns();
        $hidden = get_hidden_columns($hook_suffix);
        $sortable = array();

        $this->_column_headers = array($columns, $hidden, $sortable);
        $current_page = $this->get_pagenum();

        if ($all) {
          $limit = '';
        } else {
          $limit = ($current_page - 1) * $per_page;
          $limit = $wpdb->prepare("LIMIT %d, %d", $limit, $per_page);
        }

        $forms_data = get_option('accua_forms_saved_forms', array());

        if ($del) {
          $afs_status_cond = 'afs_status < 0';
        } else {
          $afs_status_cond = 'afs_status >= 0';
        }

        if (isset($get['lead_status'])) {
          $afs_lead_status_cond = $wpdb->prepare(" AND afs_lead_status = %d ", $get['lead_status']);
        } else {
          $afs_lead_status_cond = '';
        }


        if($search !== '') {
          $sql_search = $wpdb->prepare("'%s'", "%$search%");
          $sql_search_where = " AND (
            afs_ip LIKE {$sql_search}
            OR afs_uri LIKE {$sql_search}
            OR afs_referrer LIKE {$sql_search}
            OR afs_lang LIKE {$sql_search}
            OR afs_created LIKE {$sql_search}
            OR afs_submitted LIKE {$sql_search}
            OR afs_id LIKE {$sql_search}
            OR afs_id IN (
              SELECT DISTINCT (afsv_sub_id)
              FROM `{$wpdb->prefix}accua_forms_submissions_values`
              WHERE afsv_value LIKE {$sql_search})
            ) ";
        } else {
          $sql_search_where = '';
        }

        $query1 = "SELECT SQL_CALC_FOUND_ROWS
            afs_id AS ID,
            afs_form_id AS form_id,
            afs_post_id AS pid,
            afs_ip AS ip,
            afs_uri AS uri,
            afs_referrer AS referrer,
            afs_lang AS lang,
            afs_created AS created,
            afs_submitted AS submitted,
            afs_lead_status AS lead_status
          FROM `{$wpdb->prefix}accua_forms_submissions`
          WHERE {$afs_status_cond} {$afs_lead_status_cond} {$filter} {$sql_search_where}
          ORDER BY afs_id DESC
          {$limit}";

        $data1 = $wpdb->get_results($query1, ARRAY_A);

        $total_items = $wpdb->get_var('SELECT FOUND_ROWS()');

        $data = array();
        $submissions = array();
        foreach ($data1 as $row) {
          $fid = $row['form_id'];

          if (isset($forms_data[$fid]['title']) && (trim($forms_data[$fid]['title']) !== '')) {
            $row['form_title'] = $forms_data[$fid]['title'];
          } else {
            $row['form_title'] = $fid;
          }

          $sid = (int) $row['ID'];

          foreach($row as $k => $v) {
            $row[$k] = htmlspecialchars($v);
          }

          $data[$sid] = $row;
          $submissions[] = $sid;
        }

        if ($submissions) {
          $submissions = implode(',',$submissions);
          $query2 = "SELECT *
                FROM `{$wpdb->prefix}accua_forms_submissions_values`
                WHERE afsv_sub_id IN ($submissions) ";

          $data2 = $wpdb->get_results($query2, OBJECT);

          if ($data2) {
            foreach ($data2 as $row) {
              switch ($row->afsv_type) {
                case 'file' :
                  $fieldid = rawurlencode($row->afsv_field_id);
                  $filename = rawurlencode($row->afsv_value);
                  $url = admin_url('admin-ajax.php') . "?action=accua_forms_download_submitted_file&subid={$row->afsv_sub_id}&field={$fieldid}&file={$filename}&nonce=" . wp_create_nonce('accua_forms_download_nonce');
                  if ($this->export_xls) {
                    /* imposto token segreto 32 caratteri */
                    $token_xls = accua_forms_generate_download_token($row->afsv_sub_id);
                    $url .= '&html=1&token='.$token_xls;
                  }
                  $url = htmlspecialchars($url,ENT_QUOTES);
                  $filename = htmlspecialchars($row->afsv_value,ENT_QUOTES);
                  $fielddata = "<a href='{$url}' target='_blank'>{$filename}</a>";
                break;
                case 'colorpicker':
                  if ($row->afsv_value === '') {
                    $fielddata = '';
                  } else {
                    $value_esc = htmlspecialchars($row->afsv_value,ENT_QUOTES);
                    $fielddata = "<span style='color: {$value_esc}'><font color='{$value_esc}'>&#9608;</font></span> $value_esc";
                  }
                break;
                case 'password':
                case 'password-and-confirm':
                case 'token':
                  $fielddata = '';
                break;
                default:
                  $fielddata = htmlspecialchars($row->afsv_value,ENT_QUOTES);
              }
              $data[$row->afsv_sub_id]['_field_'.$row->afsv_field_id] = $fielddata;
            }
          }
        }

        $this->items = $data;

        $this->set_pagination_args( array(
            'total_items' => $total_items,
            'per_page'    => $per_page,
            'total_pages' => ceil($total_items/$per_page)
        ) );


    }

    function process_bulk_action(){
      $current_action = $this->current_action();
      if ($current_action) {
        check_admin_referer('bulk-submissions'); //check nonce generated for 'bulk-'.$this->_args['plural']
        if ('delete' === $current_action) {
          $trashed = array();
          if ((!empty($_GET['submission'])) && is_array($_GET['submission'])) {
            foreach ($_GET['submission'] as $i) {
              $i = (int)$i;
              $trashed[$i] = $i;
            }
          }
          if ($trashed) {
            $trashed = implode(',', $trashed);
            global $wpdb;
            $res = $wpdb->query("UPDATE `{$wpdb->prefix}accua_forms_submissions` SET afs_status = -1 WHERE afs_id in ( $trashed )");
            if ($res === false) {
              $this->set_message(__("Error moving submissions to trash.", 'contact-forms') . '<br />');
            } else if ($res == 1) {
              $this->set_message(__("Moved 1 submission to trash.", 'contact-forms') . '<br />');
            } else {
              $this->set_message(strtr(__("Moved %res submissions to trash.", 'contact-forms'), array('%res' => $res)) . '<br />');
            }
          } else {
            $this->set_message(__("No submission selected.", 'contact-forms') . '<br />');
          }
        } else if ('shred' === $current_action) {
          $shredded = array();
          if ((!empty($_GET['submission'])) && is_array($_GET['submission'])) {
            foreach ($_GET['submission'] as $i) {
              $i = (int)$i;
              $shredded[$i] = $i;
            }
          }
          if ($shredded) {
            $shredded = implode(',', $shredded);
            global $wpdb;
            $res = $wpdb->query("DELETE FROM `{$wpdb->prefix}accua_forms_submissions` WHERE afs_id in ( $shredded )");
            if ($res === false) {
              $this->set_message(__("Error deleting submissions.", 'contact-forms') . '<br />');
            } else {
              $res2 = $wpdb->query("DELETE FROM `{$wpdb->prefix}accua_forms_submissions_values` WHERE afsv_sub_id in ( $shredded )");
              if ($res == 1) {
                $this->set_message(__("Deleted 1 submission.", 'contact-forms') . '<br />');
              } else {
                $this->set_message(strtr(__("Deleted %res submissions.", 'contact-forms'), array('%res' => $res)) . '<br />');
              }
            }
          } else {
            $this->set_message(__("No submission selected.", 'contact-forms') . '<br />');
          }
        } else if ('restore' === $current_action) {
          $restored = array();
          if ((!empty($_GET['submission'])) && is_array($_GET['submission'])) {
            foreach ($_GET['submission'] as $i) {
              $i = (int)$i;
              $restored[$i] = $i;
            }
          }
          if ($restored) {
            $restored = implode(',', $restored);
            global $wpdb;
            $res = $wpdb->query("UPDATE `{$wpdb->prefix}accua_forms_submissions` SET afs_status = 0 WHERE afs_id in ( $restored )");
            if ($res === false) {
              $this->set_message(__("Error restoring submissions.", 'contact-forms') . '<br />');
            } else if ($res == 1) {
              $this->set_message(__("Restored 1 submission.", 'contact-forms') . '<br />');
            } else {
              $this->set_message(strtr(__("Restored %res submissions.", 'contact-forms'), array('%res' => $res)) . '<br />');
            }
          } else {
            $this->set_message(__("No submission selected.", 'contact-forms') . '<br />');
          }
        }
      }
    }
}

function accua_forms_submissions_list_page($head = false){
    static $listTable = null;

    global $wpdb;
    if ($listTable === null) {
      $listTable = new Accua_Forms_Submissions_List_Table();

      if (isset($_POST['action'])) {
        if ($_POST['action'] === 'trash' && !empty($_POST['submission']) && is_array($_POST['submission'])) {
          echo "yes";
          $trashed = array();
          foreach($_POST['submission'] as $i) {
            $i = (int) $i;
            $trashed[$i] = $i;
          }
          $trashed=implode(',',$trashed);

          $res = $wpdb->query("UPDATE `{$wpdb->prefix}accua_forms_submissions` SET afs_status = -1 WHERE afs_id in ( $trashed )");
          if ($res === false) {
            $listTable->set_message(__("Error moving submissions to trash.", 'contact-forms').'<br />');
          } else {
            $listTable->set_message(strtr(__("Moved %res submissions to trash.", 'contact-forms'), array('%res' => $res)).'<br />');
          }
        }
      }

      /*
      wp_enqueue_script('jquery-ui-mouse');
      wp_enqueue_script('jquery-ui-widget');
      */

      wp_enqueue_script('jquery');
      wp_enqueue_script('jquery-ui-core');
      wp_enqueue_script('jquery-ui-sortable');
      wp_enqueue_script('accua-forms-set-lead-status', plugins_url('/js/accua-forms-set-lead-status.js', ACCUA_FORMS_FILE ), array( 'jquery', 'wp-pointer'), ACCUA_FORMS_JS_VERSION);

      $listTable->process_bulk_action();
      $listTable->prepare_items();
    }

    if ($head === true) {
      return;
    }
?>
<style>
.tablenav.bottom {
    float: left;
    width: 100%;
}

.accua_tablenav {
  float: left;
}

.esportazione {
  margin-left:10px;
}

.esportazione a {
    cursor: pointer;
    line-height: 28px;
    text-decoration: underline;
}
.tablenav.top {
    clear: none;
    float: left;
    width: 100%;
}
</style>
    <div id="accua_forms_submissions_list_page" class="accua_forms_admin_page wrap">

        <h2 style="margin-bottom: 20px;" ><img src="<?php echo ACCUA_FORMS_DIR_URL.'img/cimatti-icon-20.png'; ?>"/> <?php _e('Contact Forms - Submissions', 'contact-forms'); ?></h2>

        <?php $filter_form = $filter_post = $filter_search = $filter_year = $filter_month = NULL ;
        $filter = '';
        if(isset($_GET['_wp_http_referer'])) {
          foreach (explode('&', $_GET['_wp_http_referer']) as $coppia) {
            $param = explode("=", $coppia);

            if($param[0]=='fid' && $param[1]!='-1') { $filter_form = $param[1];  }
            if($param[0]=='pid' && $param[1]!='-1') { $filter_post = (int) $param[1];  }
            if($param[0]=='year' && $param[1]>0) { $filter_year = (int) $param[1];  }
            if($param[0]=='month' && $param[1]>0) { $filter_month = (int) $param[1];  }
            if($param[0]=='s' && $param[1]!='') { $filter_search =$param[1]; $filter.= "&amp;s=".htmlspecialchars($filter_search, ENT_QUOTES); ?>
              <script type="text/javascript">
              <!--
              jQuery(document).ready(function($) {
                jQuery('#search_id-search-input').val(<?php echo _accua_forms_json_encode($filter_search); ?>);
              });
              //-->
              </script>
            <?php
            }
          }
        }

        if(isset($_GET['fid']) && ($_GET['fid'])!=-1) {
           $filter_form = $_GET['fid'];
        }
        if(isset($_GET['pid']) && ($_GET['pid']!=-1)) {
          $filter_post = (int) $_GET['pid'];
        }
        if(isset($_GET['year']) && ($_GET['year']>0)) {
          $filter_year = (int) $_GET['year'];
        }
        if(isset($_GET['month']) && ($_GET['month']>0)) {
          $filter_month = (int) $_GET['month'];
        }

        if(isset($_GET['s']) && ($_GET['s']!='')) {
          $filter_search = $_GET['s'];
          $filter.= "&amp;s=".htmlspecialchars($filter_search, ENT_QUOTES);
        }

        $del = isset($_GET['del']) && ($_GET['del']==1);
        if (isset($_GET['lead_status'])) {
          $active_lead_status = (int) $_GET['lead_status'];
        } else {
          $active_lead_status = NULL;
        }
        ?>

       <?php if($listTable->get_message()!=NULL) { ?>
          <div class="updated"><p><?php echo $listTable->get_message(); ?></p></div>
       <?php } ?>

       <ul class="subsubsub">
          <li>
            <a <?php if (!($del || isset($active_lead_status))) { echo " class='current' "; } ?> href="admin.php?page=accua_forms_submissions_list">
            <?php _e("Active", 'contact-forms'); ?></a> (<?php echo $listTable->get_num_of_active_items(); ?>)
          </li>
          <li>&nbsp;|&nbsp;
            <a <?php if ($del) { echo " class='current' "; } ?> href="admin.php?page=accua_forms_submissions_list&amp;del=1">
            <?php _e("Trash", 'contact-forms'); ?></a> (<?php echo $listTable->get_num_of_del_items(); ?>)
          </li>
           <?php
           $lead_statuses = accua_forms_get_lead_statuses();
           $items_per_lead_status = $listTable->get_items_per_lead_status();
           $lead_status_title = htmlspecialchars(__('Lead status', 'contact-forms'),ENT_QUOTES);
           foreach ($lead_statuses as $lead_status_id => $lead_status_label) {
             if (!empty($items_per_lead_status[$lead_status_id]->n)) {
               $lead_status_label = htmlspecialchars($lead_status_label);
               $lead_status_class = ($active_lead_status === ((int)$lead_status_id)) ? " class='current' " : '';
               echo "<li>&nbsp;|&nbsp;<a {$lead_status_class} href='admin.php?page=accua_forms_submissions_list&amp;lead_status={$lead_status_id}' 
                         title='{$lead_status_title}'>{$lead_status_label}</a> ({$items_per_lead_status[$lead_status_id]->n})</li>";
             }
           }
           ?>
       </ul>

        <div>
       <p style="clear:both;"><?php _e("Use the screen options to add or remove columns from the table below. Only the visible columns will be exported.", 'contact-forms'); ?></p>
       <form style="margin-top: 20px;" id="submissions-action" method="get" action="">
            <!-- For plugins, we also need to ensure that the form posts back to our current page -->
              <input type="hidden" name="page" value="<?php if (isset($_REQUEST['page'])) { echo htmlspecialchars(stripslashes($_REQUEST['page'])); } ?>" />
              <input type="hidden" name="del" value="<?php if (isset($_REQUEST['del'])) { echo htmlspecialchars(stripslashes($_REQUEST['del'])); } ?>" />
               <!-- SEARCH FORM -->
               <?php $listTable->search_box(__("Search", 'contact-forms'), 'search_id'); ?>
               <!-- FINE SEARCH FORM -->
        <!-- FILTRI -->
        <div class="accua_tablenav">
        <?php
            //tutti i form salvati e non

            $saved_forms_id = array();
            $forms_data = get_option('accua_forms_saved_forms', array());
            foreach($forms_data as $single_form_key=>$single_form){
              $saved_forms_id[] = $wpdb->prepare("%s", $single_form_key);
            }
            $saved_forms_id_string = implode(',',$saved_forms_id);

            if ($saved_forms_id_string) {
              $id_del_other_forms = $wpdb->get_results("SELECT distinct afs_form_id
                  FROM {$wpdb->prefix}accua_forms_submissions WHERE afs_form_id NOT IN ($saved_forms_id_string) AND afs_status >= 0", ARRAY_A);
            } else {
              $id_del_other_forms = array();
            }


            //post submissions
            $id_posts = $wpdb->get_results("SELECT distinct afs_post_id
			          FROM {$wpdb->prefix}accua_forms_submissions WHERE afs_post_id <> 0 AND afs_status >= 0 ", ARRAY_A);

            $cur_year = (int) date('Y');
            $min_year = (int) $wpdb->get_var("SELECT year(min(afs_submitted)) FROM {$wpdb->prefix}accua_forms_submissions");
            if ($min_year <= 0) {
              $min_year = $cur_year;
            }

            ?>

            <span><label><?php _e("Page: ", 'contact-forms'); ?></label><select name='pid' id='pid'>
              <option value="-1" selected ><?php _e("Show all pages", 'contact-forms'); ?></option>
              <?php foreach($id_posts as $id_post){
                  	 echo "<option value='".$id_post['afs_post_id']."'";
                  	 if($filter_post==$id_post['afs_post_id']) {
                        echo " selected ";
                        $filter.= "&amp;pid=".$filter_post;
                     }
                  	 echo ">".get_the_title($id_post['afs_post_id'])."</option>";
                    } ?>
              </select></span>
              <span style="margin-left: 10px;"><label><?php _e("Forms: ", 'contact-forms'); ?></label><select name='fid' id='fid'>
              <option value="-1" selected > <?php _e("Show all forms", 'contact-forms'); ?> </option>
              <?php foreach($forms_data as $single_form_key=>$single_form){
                  	 echo "<option value='".$single_form_key."'";
                  	 if($filter_form==$single_form_key) {
                          echo " selected ";
                          $filter.= "&amp;fid=".$filter_form;
                     }
                  	 echo ">";
                  	 if($single_form['title']!=NULL)
                  	   echo htmlspecialchars($single_form['title']); //TITOLO
                  	 else
                  	   echo $single_form_key; //ID
                  	 echo "</option>";
                  	 }
                  	 ///no saved forms///
                  	 foreach($id_del_other_forms as $id_del_other_form) {
                  	   echo "<option value='".$id_del_other_form['afs_form_id']."'";
                        if($filter_form==$id_del_other_form['afs_form_id']) {
                          echo " selected ";
                          $filter.= "&amp;fid=".$filter_form;
                        }
                        echo $id_del_other_form['afs_form_id']." (del) </option>";
                  	 }

                     ?>
              </select>
              </span>

              <span style="margin-left: 10px;"><label><?php _e("Year: ", 'contact-forms'); ?></label><select name='year' id='year'>
              <option value="-1" ></option>
              <?php for($i = $min_year; $i <= $cur_year; $i++){
                       echo "<option value='".$i."'";
                       if($filter_year==$i) {
                          echo " selected ";
                          $filter.= "&amp;year=".$i;
                       }
                       echo ">$i</option>";
                     }
                     ?>
              </select>
              </span>

              <span style="margin-left: 10px;"><label><?php _e("Month: ", 'contact-forms'); ?></label><select name='month' id='month'>
              <option value="-1" ></option>
              <?php for($i = 1; $i <= 12; $i++){
                       echo "<option value='".$i."'";
                       if($filter_month==$i) {
                          echo " selected ";
                          $filter.= "&amp;month=".$i;
                       }
                       echo ">$i</option>";
                     }
                     ?>
              </select>
              </span>

              <span>
              <input type="submit" value="<?php _e("Filter", 'contact-forms'); ?>" class="button-secondary action" name=""></span>
          </form>
          </div>
          <!-- FINE FILTRI -->
           <script type="text/javascript">
          <!--
             function set_parameter(sel_column) {
               var stringa ='';
               var name_action = ajaxurl + "?action=accua_forms_submission_page_save_excel<?php
                 if ($del) {
                   echo '&del=1';
                 } if (isset($active_lead_status)) {
                   echo '&lead_status=',$active_lead_status;
                 } ?>";
               if(sel_column) {
                 jQuery('#adv-settings input[type=checkbox]:checked').each(function() {
                   stringa += this.value + "%2C";
                 });
               }
               else {
                 jQuery('#adv-settings input[type=checkbox]').each(function() {
                   stringa += this.value + "%2C";
                 });
               }
               var search = jQuery("#search_id-search-input").val();
               var pid = jQuery("select#pid").val();
               var fid = jQuery("select#fid").val();
               var year = jQuery("select#year").val();
               var month = jQuery("select#month").val();
               if (search) name_action+='&s='+search;
               if (pid) name_action+='&pid='+pid;
               if (fid) name_action+='&fid='+fid;
               if (year) name_action+='&year='+year;
               if (month) name_action+='&month='+month;
               if(stringa) name_action+= '&accua_show_field='+stringa;

               if(sel_column) { jQuery('#esporta_link_visible_column').attr("href",name_action); }
               else jQuery('#esporta_link_all_column').attr("href",name_action);

             }
             //-->
           </script>
       <!-- ESPORTAZIONE -->

       <div class="accua_tablenav esportazione wp-core-ui ">
         <a onclick ="set_parameter(1);" id="esporta_link_visible_column" class="button-primary"><?php _e("Export visible columns to Excel", 'contact-forms'); ?></a>
         <a onclick ="set_parameter(0);" id="esporta_link_all_column" class="button-primary"><?php _e("Export all columns to Excel", 'contact-forms'); ?></a>
       </div>

       <!-- FINE ESPORTAZIONE -->
       <form style="margin-top: 20px;" id="submissions-action" method="get" action="">
       <!-- Now we can render the completed list table -->
         <input type="hidden" name="page" value="<?php  if (isset($_REQUEST['page'])) { echo htmlspecialchars(stripslashes($_REQUEST['page'])); } ?>" />
         <input type="hidden" name="del" value="<?php  if (isset($_REQUEST['del'])) { echo htmlspecialchars(stripslashes($_REQUEST['del'])); } ?>" />
            <?php $listTable->display(); ?>
       </form>
       <?php /* <pre>$listTable = <?php echo htmlspecialchars(print_r($listTable, true)); ?></pre> */ ?>
    </div>

    <?php
  if (!class_exists('AccuaFormsHelp')) {
    require_once('accua-forms-help.php');
  }
  $accuaHelp = AccuaFormsHelp::getInstance();
  $accuaHelp->finished();
}




