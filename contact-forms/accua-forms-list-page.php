<?php
if(!class_exists('WP_List_Table')){
    require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );
}

class Accua_Forms_List_Table extends WP_List_Table {

    var $message = NULL;
    var $active_items = 0;
    var $del_items = 0;

    var $accua_orderby = null;
    var $accua_order_asc = null;

    function __construct(){
        global $status, $page;
        $this->message = '';
        parent::__construct( array(
            'singular'  => 'form',
            'plural'    => 'forms',
            'ajax'      => false
        ) );
    }

    function get_num_of_active_items () {
      return $this->active_items;
    }

    function get_num_of_del_items () {
      return $this->del_items;
    }

    function column_default($item, $column_name){
      if (isset($item[$column_name])) {
        return htmlspecialchars($item[$column_name], ENT_QUOTES);
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

    function column_title($item){
      if ($item['deleted']) {
        if (isset($item['title'])) {
          $item_name = htmlspecialchars($item['title']);
        } else {
          $item_name = $item['ID'];
        }
        $nonced_edit = wp_nonce_url('admin.php?page=accua_forms_list&amp;fid=' . $item['ID'] . '&amp;restore=1', 'edit_posts');
        $actions = array(
            'edit' => "<a href='" . $nonced_edit . "'>".__("Restore", 'contact-forms')."</a>",
        );
        return "<strong>".$item_name."</strong>".$this->row_actions($actions);
      } else {
        $nonced_edit = wp_nonce_url('admin.php?page=accua_forms_list&amp;fid=' . $item['ID'], 'edit_posts');
        $nonced_clone = wp_nonce_url('admin.php?page=accua_forms_add&amp;clonefrom=' . $item['ID'], 'clone_posts');
        $nonced_del = wp_nonce_url('admin.php?page=accua_forms_list&amp;fid_del=' . $item['ID'], 'trash_posts');
        $actions = array(
            'edit' => "<a href='{$nonced_edit}'>".__("Edit", 'contact-forms')."</a>"
                . " &middot; <a href='{$nonced_clone}'>".__("Clone", 'contact-forms')."</a>"
                . " &middot; <a href='admin.php?page=accua_forms_submissions_list&amp;fid={$item['ID']}'>".__("Report", 'contact-forms')."</a>"
                . " &middot; <a href='{$nonced_del}'>".__("Trash", 'contact-forms')."</a>",
        );
        return "<strong><a class='row-title' href='{$nonced_edit}'>".htmlspecialchars($item['title'])."</a></strong>".$this->row_actions($actions);
      }
    }




   function column_submissions($item){
        if($item['submissions']!=0)
        return sprintf(
            '<a href="admin.php?page=accua_forms_submissions_list&amp;fid=%1$s">%2$s</a>',
            /*$1%s*/ $item['ID'],
            /*$2%s*/ $item['submissions']
        );
        else
          return "0";
    }


    function get_columns(){
        $columns = array(
            'cb' => '<input type="checkbox" />', //Render a checkbox instead of text
            'title' =>  __('Title', 'contact-forms'),
            'ID' => 'ID',
            'shortcode' => __('Shortcode', 'contact-forms'),
            'phpcode' => __('PHP code', 'contact-forms'),
            'fromemail' => __('From Email', 'contact-forms'),
            'fromname' => __('From Name', 'contact-forms'),
            'adminemail' => __('Admin Email', 'contact-forms'),
            'bccemail' => __('BCC Email', 'contact-forms'),
            'submissions' => __('Submissions', 'contact-forms'),
        );
        return $columns;
    }

    function get_sortable_columns() {
      return array(
          'title' => array('title', false),
          'ID' => array('ID', false),
          'submissions' => array('submissions', false),
      );
    }

    function get_bulk_actions() {
      if(isset($_GET['del']) && $_GET['del']==1) {
        $actions = array();
      } else {
        $actions = array(
            'delete' => __('Move to trash', 'contact-forms')
        );
      }
      return $actions;
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

    function accua_usort_numeric( $a, $b ) {
      $result = ((int)$a[$this->accua_orderby]) - ((int)$b[$this->accua_orderby]);
      if (!$result) {
        $result = ((int)$a['ID']) - ((int)$b['ID']);
      }
      return ( $this->accua_order_asc ) ? $result : -$result;
    }

    function accua_usort_string( $a, $b ) {
      $result = strcasecmp($a[$this->accua_orderby], $b[$this->accua_orderby]);
      if (!$result) {
        $result = ((int)$a['ID']) - ((int)$b['ID']);
      }
      return ( $this->accua_order_asc ) ? $result : -$result;
    }

    function process_bulk_action() {
      
      $forms = (isset($_GET['form']) && is_array($_GET['form'])) ? $_GET['form'] : array();
      if('delete'===$this->current_action() ) {
        $forms_deleted = false;
        $forms_data = get_option('accua_forms_saved_forms', array());
        $trash_data = get_option('accua_forms_trash_forms', array());

        foreach($forms as $single_form) {
          if(isset($forms_data[$single_form])) {
            $trash_data[$single_form] = $forms_data[$single_form];
            unset($forms_data[$single_form]);
            $forms_deleted = true;
            ?>
            <script type="text/javascript">
            jQuery(function($){
                jQuery("#the-list tr:has(th.check-column input[type='checkbox'][value='<?php echo $single_form; ?>'])").fadeOut();
            });
            </script>
            <?php
          }
        }
        if ($forms_deleted) {
          update_option('accua_forms_trash_forms', $trash_data);
          update_option('accua_forms_saved_forms', $forms_data);
          $this->set_message(__("Forms deleted", 'contact-forms'));
        }

      }

    }

    function prepare_items() {
        global $wpdb, $hook_suffix;
        $per_page = 50;
        $del = isset($_GET['del']) && $_GET['del']==1;

        $columns = $this->get_columns();
        $hidden = get_hidden_columns($hook_suffix);
        $sortable = $this->get_sortable_columns();


        $this->_column_headers = array($columns, $hidden, $sortable);

        $current_page = $this->get_pagenum();

        $limit = ($current_page - 1) * $per_page;

        $forms_data = get_option('accua_forms_saved_forms', array());
        $forms_trash = get_option('accua_forms_trash_forms', array());
        $forms_default = get_option('accua_forms_default_form_data',array());

        $this->active_items = count($forms_data);
        $this->del_items = count($forms_trash);

        $data = array();

        if ($del) {
          check_admin_referer('deleted_forms');
          $start = $forms_trash;
        } else {
          $start = $forms_data;
        }

        foreach ($start as $id => $form) {
          if (isset($form['title']) && (trim($form['title']) !== '')) {
            $title = $form['title'];
          } else {
            $title = __('(no title)','contact-forms');
          }

          $data[$id] = array (
            'ID' => $id,
            'title' => $title,
            'submissions' => 0,
            'shortcode'=> $del?'':'[accua-form fid="'.$id.'"]',
            'phpcode'=> $del?'':"<?php accua_forms_include('$id'); ?>",
            'deleted' => $del,
          );

          $forminfos = array(
            'fromemail' => 'emails_from',
            'fromname' => 'emails_from_name',
            'adminemail' => 'admin_emails_to',
            'bccemail' => 'emails_bcc',
          );

          foreach ($forminfos as $infodest => $infoid) {
            if (isset($form[$infoid])) {
              $data[$id][$infodest] = $form[$infoid];
            } else {
              $data[$id][$infodest] = $forms_default[$infoid] . " (default)";
            }
          }

        }

        $query = "SELECT afs_form_id AS fid, count(*) AS submissions
              FROM `{$wpdb->prefix}accua_forms_submissions`
              WHERE afs_status >= 0
              GROUP BY afs_form_id";

        $submissions = $wpdb->get_results($query, OBJECT);
        foreach ($submissions as $fsub) {
          if (isset($data[$fsub->fid])) {
            $data[$fsub->fid]['submissions'] = $fsub->submissions;
          } else if ($del){
            if (!isset($forms_data[$fsub->fid])) {
              $data[$fsub->fid] = array(
                'ID' => $fsub->fid,
                'submissions' => $fsub->submissions,
                'deleted' => true,
                'shortcode' => '',
                'phpcode' => '',
                'fromemail' => '',
                'fromname' => '',
                'adminemail' => '',
                'bccemail' => '',
              );
              $this->del_items++;
            }
          } else if (!isset($forms_trash[$fsub->fid])) {
            $this->del_items++;
          }
        }

        $this->accua_order_asc = empty($_GET['order']) || ($_GET['order'] != 'desc');
        $this->accua_orderby = (isset($_GET['orderby'])) ? $_GET['orderby'] : 'title';
        switch($this->accua_orderby) {
          case 'ID':
          case 'submissions':
            $this->accua_orderby = $_GET['orderby'];
            @ usort( $data, array( &$this, 'accua_usort_numeric' ) );
          break;
          //case 'title':
          default:
            $this->accua_orderby = 'title';
            @ usort( $data, array( &$this, 'accua_usort_string' ) );
        }

        $total_items = count($data);

        $this->items = array_slice($data,$limit,$per_page);

        $this->set_pagination_args( array(
            'total_items' => $total_items,
            'per_page'    => $per_page,
            'total_pages' => ceil($total_items/$per_page)
        ) );

    }

}

function accua_forms_list_page_table($head = false){

  static $listTable = null;
	//var_dump($_REQUEST);
	
  if(isset($_GET['fid_del'])) {
    check_admin_referer('trash_posts');
    $fid = $_GET['fid_del'];
    $forms_data = get_option('accua_forms_saved_forms', array());
    if (isset($forms_data[$fid])) {
      $trash_data = get_option('accua_forms_trash_forms', array());
      $trash_data[$fid] = $forms_data[$fid];
      update_option('accua_forms_trash_forms', $trash_data);
      unset($forms_data[$fid]);
      update_option('accua_forms_saved_forms', $forms_data);
    }
  }


  if ($listTable === null) {
    $listTable = new Accua_Forms_List_Table();
    $listTable->process_bulk_action();
    $listTable->prepare_items();
  }

  if ($head === true) {
    return;
  }

  if($listTable->get_message()!=NULL) { ?>
    <div class="updated"><p><?php echo $listTable->get_message(); ?></p></div>
  <?php } ?>
  <ul class="subsubsub">
    <li>
        <?php $nonced_url = wp_nonce_url('admin.php?page=accua_forms_list', 'home_forms') ?>
      <a <?php if (!isset($_GET['del']) || ($_GET['del']!=1)) { echo " class='current' "; } ?> href="<?php echo $nonced_url ?>">
      <?php _e("Active", 'contact-forms'); ?></a> (<?php echo $listTable->get_num_of_active_items(); ?>) |
    </li>
    <li>
        <?php $nonced_url = wp_nonce_url('admin.php?page=accua_forms_list&del=1', 'deleted_forms'); ?>
      <a  <?php if (isset($_GET['del']) && ($_GET['del']==1)) { echo " class='current' "; } ?> href="<?php echo $nonced_url ?>">
      <?php _e("Trash", 'contact-forms'); ?></a> (<?php echo $listTable->get_num_of_del_items(); ?>)
    </li>
  </ul>

  <form id="accua-forms-list-filter" method="get">
    <input type="hidden" name="page" value="<?php echo htmlspecialchars(stripslashes($_REQUEST['page'])); ?>" />
    <?php $listTable->display(); ?>
  </form>
  <?php /* echo '<pre>$listTable = ' htmlspecialchars(print_r($listTable, true)); ?></pre> */ ?>
<?php
}
