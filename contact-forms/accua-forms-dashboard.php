<?php function _accua_forms_dashboard_page(){
  /* global vars */
  global $hook_suffix;
  /* enable add_meta_boxes function in this page. */
  do_action( 'add_meta_boxes', $hook_suffix, 10, 2 ); ?>

  <div class="fx-settings-meta-box-wrap">
    <div id="postbox-container-1" class="postbox-container">
    </div><!-- .fx-settings-meta-box-wrap -->
  </div>
  <?php wp_nonce_field( 'closedpostboxes', 'closedpostboxesnonce', false ); ?>
  <?php wp_nonce_field( 'meta-box-order', 'meta-box-order-nonce', false );

  global $wpdb;
  $email_fields = array();
  $num_submissions = $wpdb->get_var("SELECT count(*) as num_row
      FROM {$wpdb->prefix}accua_forms_submissions WHERE afs_status >= 0");
  if ($num_submissions) {
    $num_posts = $wpdb->get_var("SELECT count(DISTINCT afs_post_id ) as num_row
      FROM {$wpdb->prefix}accua_forms_submissions WHERE afs_post_id <> 0 AND afs_status >= 0");
    $num_forms = $wpdb->get_var("SELECT count(DISTINCT afs_form_id ) as num_row
      FROM {$wpdb->prefix}accua_forms_submissions WHERE afs_status >= 0");
    $num_submissions_per_lead_status = $wpdb->get_results("SELECT afs_lead_status AS status, COUNT(*) AS n
                   FROM `{$wpdb->prefix}accua_forms_submissions`
                   WHERE afs_status >= 0
                   GROUP BY afs_lead_status", OBJECT_K);

    //seleziono i campi email
    $avail_fields = get_option('accua_forms_avail_fields', array());
    foreach ($avail_fields as $key => $single_field) {
      if ($single_field['type'] == 'email' || $single_field['type'] == 'autoreply_email') {
        $email_fields[] = $wpdb->prepare('%s', $key);
      }
    }
    if ($email_fields) {
      $email_fields_string = implode(',', $email_fields);
      $num_emails = $wpdb->get_var("SELECT COUNT(DISTINCT (`afsv_value`)) as num_row
      FROM {$wpdb->prefix}accua_forms_submissions, {$wpdb->prefix}accua_forms_submissions_values
      WHERE `afs_id` = `afsv_sub_id`
      AND afsv_field_id IN ({$email_fields_string})
      AND afs_status >= 0");
    } else {
      $num_emails = 0;
    }
  }


  $forms_data = get_option('accua_forms_saved_forms', array());

  $fid = '';
  $fid_param = '';
  if (isset($_GET['fid'])) {
    $fid = stripslashes( (string) $_GET['fid'] );
    if (isset($forms_data[$fid])) {
      $fid_param = $wpdb->prepare('AND afs_form_id = %s', $fid);
    } else {
      $fid = '';
    }
  }


  if (isset($_GET['period'])) {
    $period = (int) $_GET['period'];
    if($period <= 0 && $period != -1){
      $period = 24; //default
    }
  } else{
    $period = 24; //default
  }

  if(isset($_POST['del_sub_form'])) {
    $id = (int)$_POST['del_sub_form'];
    check_admin_referer("del_sub_form_{$id}", "_wpnonce_del_sub_form");
    accua_forms_trash_submission($id);
  }

  $label_my = array();
  $submissions = array();
  $unique_submissions = array();
  $origin_label_my = array();

  if ($email_fields) {
    //costruisco il grafico
    $query_grafico = $wpdb->get_results("SELECT YEAR( afs_submitted ) AS `year` , MONTH( afs_submitted ) AS `month` , COUNT( DISTINCT (`afs_id`) ) AS `submissions` , COUNT( DISTINCT (`afsv_value`)) AS `unique_submissions`
    FROM `{$wpdb->prefix}accua_forms_submissions`
      LEFT JOIN `{$wpdb->prefix}accua_forms_submissions_values` ON `afs_id` = `afsv_sub_id`
    WHERE afsv_field_id IN ({$email_fields_string})
    AND afs_status >= 0
    $fid_param
    GROUP BY `year` , `month`
    ORDER BY `year` DESC , `month` DESC");

    if ($query_grafico) {
      /* cicliamo per ogni mese in modo da riempire con 0 i mesi senza compilazioni */
      for ($i = date('Y'); $i >= $query_grafico[count($query_grafico) - 1]->year; $i--) {
        $start_month = 12;
        $end_month = 1;
        if ($i == date('Y')) $start_month = date('n');
        if ($i == $query_grafico[count($query_grafico) - 1]->year) $end_month = $query_grafico[count($query_grafico) - 1]->month;
        for ($j = $start_month; $j >= $end_month; $j--) {
          $find = false;
          foreach ($query_grafico as $result) {
            if (!$find && (int)$result->year == $i && (int)$result->month == $j) {
              $label_my[] = $j . '/' . $i;
              $submissions[] = (int)$result->submissions;
              $unique_submissions[] = (int)$result->unique_submissions;
              $find = true;
            }
          }
          if (!$find) {
            $label_my[] = $j . '/' . $i;
            $submissions[] = 0;
            $unique_submissions[] = 0;

          }
        }
      }
      $origin_label_my = $label_my;
      $origin_submissions = $submissions;
      $origin_unique_submissions = $unique_submissions;

      if ($period != -1) {
        $label_my = array_slice($label_my, 0, $period);
        $submissions = array_slice($submissions, 0, $period);
        $unique_submissions = array_slice($unique_submissions, 0, $period);
      }

      $label_my = array_reverse($label_my);
      $submissions = array_reverse($submissions);
      $unique_submissions = array_reverse($unique_submissions);
    }
  }


  ?>

  <div id="accua_dashboard_page" class="accua_forms_admin_page wrap">
    <h2><img src="<?php echo ACCUA_FORMS_DIR_URL.'img/cimatti-icon-20.png'; ?>"/> <?php _e('Contact Forms - Dashboard', 'contact-forms'); ?></h2>
    <div class="metabox-holder accua-forms-metabox-holder">
      <div class="postbox ">
        <h2><span><?php _e('Monthly number of forms submitted', 'contact-forms'); ?></span></h2>
        <div class="inside" id="dashboard_right_now">

          <form method="get">
            <input type="hidden" name="page" value="accua_forms" />

            <select name="period">
              <option value='3' <?php if($period == 3){ echo 'selected="selected"'; } ?>><?php _e('last', 'contact-forms'); ?> 3 <?php _e('months', 'contact-forms'); ?></option>
              <option value='6' <?php if($period == 6){ echo 'selected="selected"'; } ?>><?php _e('last', 'contact-forms'); ?> 6 <?php _e('months', 'contact-forms'); ?></option>
              <option value='12' <?php if($period == 12){ echo 'selected="selected"'; } ?>><?php _e('last', 'contact-forms'); ?> 12 <?php _e('months', 'contact-forms'); ?></option>
              <option value='24' <?php if($period == 24 || !$period ){ echo 'selected="selected"'; } ?>><?php _e('last', 'contact-forms'); ?> 24 <?php _e('months', 'contact-forms'); ?></option>
              <option value='-1' <?php if($period == -1 ){ echo 'selected="selected"'; } ?>><?php _e('All', 'contact-forms'); ?></option>
            </select>

            <select name="fid">
              <?php
              $selected = ($fid === '') ? 'selected="selected"' : '';
              echo "<option value='' $selected >", __('Submissions from all forms', 'contact-forms'),'</option>';
              foreach($forms_data as $ffid => $form) {
                $selected = ($fid == $ffid) ? 'selected="selected"' : '';
                $title = isset($form['title']) ? trim($form['title']) : '';
                if ($title === '') {
                  $title = $ffid;
                }
                $ffid = htmlspecialchars($ffid, ENT_QUOTES);
                $title = htmlspecialchars($title, ENT_QUOTES);
                echo "<option value='$ffid' $selected >$title</option>";
              }
              ?>
            </select>

            <input type="submit" class="button button-secondary" value="<?php _e('Apply', 'contact-forms') ?>" />
          </form>


          <form method="get" action="" style="margin-bottom:70px;">
            <div class="div-inside-form-slider">
              <div>
                <input type="range" min="1" max="<?php echo count($origin_label_my); ?>" value="24" class="slider" name="slide_period" id="slide_period" step="1">
                <p>Last <span id="slide_period_value" class="span-value"></span>  months</p>
              </div>
            </div>
          </form>

          <?php
          if (!empty($query_grafico)) {

            echo "<div class='fixed-height-chart' style='height: 350px'>";
            echo '<canvas id="ContactChart"></canvas>';
            echo "</div>";

            $script_grafico = "
             <script> 
             jQuery(document).ready(function(){
             /* slider */
                 var slider_periodo = document.getElementById('slide_period');
                 var output_slider_periodo = document.getElementById('slide_period_value');
                 output_slider_periodo.innerHTML = slider_periodo.value;
                 
                  var labels = " . _accua_forms_json_encode($label_my) . ";  
                  const dataa = {
                    labels: labels,
                    datasets: [
                        {
                          label: 'Submissions', 
                          data: " . _accua_forms_json_encode($submissions) . ",
                          backgroundColor: 'rgba(0, 0, 0, 0.1)',                
                        borderWidth: 1,
                        },
                        {
                          label: 'Unique submissions',
                          data: " . _accua_forms_json_encode($unique_submissions) . ",
                          backgroundColor: 'rgba(255, 114, 5, 1)',                  
                          borderWidth: 1,
                          xAxisID: 'axis1',
                        }              
                    ]
                  };  
          
         const config = {
            type: 'bar',
            data: dataa,                 
            options: { 
              responsive: true,
              scales:{
                x: { 
                    ticks:{ 
                        display: false
                    },
                    grid: {
                        offset:false
                    }
                } 
              },
              maintainAspectRatio: false, //per avere altezza fissa
              plugins: {
                title: {
                  display: false,
                },
              },
              interaction: {
                intersect: false,
              }
            }
          };
          
          var ctx = document.getElementById('ContactChart').getContext('2d');
          var myChart = new Chart(ctx, config);
          //console.log('chart initialized', myChart);
           
           function update_mychart_submissions(item, index) {
                myChart.data.datasets[0].data[index] = item; 
           }
           function update_mychart_unique_submissions(item, index) {
                myChart.data.datasets[1].data[index] = item; 
           }
           
           slider_periodo.oninput = function() {                    
                slider_periodo_val = this.value;
                output_slider_periodo.innerHTML = slider_periodo_val;
                
                /* mi devo passare gli array nel js e tagliarli a seconda del punto in cui sono nello slider */
                label = " . _accua_forms_json_encode($origin_label_my) . ";    
                submissions = " . _accua_forms_json_encode($origin_submissions) . ";
                unique_submission = " . _accua_forms_json_encode($origin_unique_submissions) . ";
                
                label = label.slice(0, slider_periodo_val).reverse();
                submissions = submissions.slice(0, slider_periodo_val).reverse();
                unique_submission = unique_submission.slice(0, slider_periodo_val).reverse();
                
                submissions.forEach(update_mychart_submissions);
                unique_submission.forEach(update_mychart_unique_submissions);
                myChart.data.labels = label
                myChart.update();            
           } 
           
           });
          </script>
          ";
            echo $script_grafico;
          } else {
            echo '<div style="height: 350px"><p>', __('No forms submitted', 'contact-forms'), '</p></div>';
          } ?>
        </div>

      </div>


      <div class="postbox ">
        <h2><span><?php _e('Last 10 submissions', 'contact-forms'); ?></span></h2>

        <?php
        $query_last_submissions = "SELECT `afs_id`, `afs_submitted`
                  FROM `{$wpdb->prefix}accua_forms_submissions`
                  WHERE `afs_status` >= 0
                  ORDER BY `afs_id` DESC LIMIT 10";

        $data_last_submissions = $wpdb->get_results($query_last_submissions);

        if ($data_last_submissions) { ?>
          <table class="table_last_submit">
            <tr>
              <th><?php _e('Date', 'contact-forms'); ?></th>
              <!--<th><?php // _e('Firstname Lastname', 'contact-forms'); ?></th>-->
              <th><?php _e('Email', 'contact-forms'); ?></th>
              <th><?php _e('Referrer', 'contact-forms'); ?></th>
              <th><?php _e('URI', 'contact-forms'); ?></th>
              <!--<th><?php // _e('Status', 'contact-forms'); ?></th>-->
              <th><?php _e('Review', 'contact-forms'); ?></th>
            </tr>



            <?php
            foreach ($data_last_submissions as $row) {
              /* problema: i campi nome e cognome sono spesso diversi
              in origine non erano creati in modo predefinito e in ogni sito possono essere diversi.
              Ora sono di default sono first_name e last_name
              ma comunque anche ora possono essere eliminati e se ne possono usare altri

              $query_meta_submission_nome = "SELECT `afsv_value`
                  FROM `{$wpdb->prefix}accua_forms_submissions_values`
                  WHERE `afsv_sub_id` = %d
                  AND `afsv_field_id` LIKE 'nome'";

              $query_meta_submission_cognome = "SELECT `afsv_value`
                  FROM `{$wpdb->prefix}accua_forms_submissions_values`
                  WHERE `afsv_sub_id` = %d
                  AND `afsv_field_id` LIKE 'cognome'";
              */
              $query_meta_submission_email = "SELECT `afsv_value`
                  FROM `{$wpdb->prefix}accua_forms_submissions_values`
                  WHERE `afsv_sub_id` = %d 
                  AND `afsv_field_id` LIKE 'email'";

              $query_data_submission_uri = "SELECT `afs_uri`
              FROM `{$wpdb->prefix}accua_forms_submissions`
              WHERE `afs_id` = %d";
              $query_data_submission_ref = "SELECT `afs_referrer`
              FROM `{$wpdb->prefix}accua_forms_submissions`
              WHERE `afs_id` = %d";

              //$data_meta_submission_nome = $wpdb->get_var($wpdb->prepare($query_meta_submission_nome, $row->afs_id));
              //$data_meta_submission_cognome = $wpdb->get_var($wpdb->prepare($query_meta_submission_cognome, $row->afs_id));
              $data_meta_submission_email = (string) $wpdb->get_var($wpdb->prepare($query_meta_submission_email, $row->afs_id));
              $data_meta_submission_uri = (string) $wpdb->get_var($wpdb->prepare($query_data_submission_uri, $row->afs_id));
              $data_meta_submission_ref = (string) $wpdb->get_var($wpdb->prepare($query_data_submission_ref, $row->afs_id));

              echo '<tr>';
              $date = new DateTime($row->afs_submitted);
              if(get_locale() == 'it_IT'){
                echo '<td>'.$date->format('j M Y').'</td>'; //todo: date in italiano? strftime?
              } else{
                echo '<td>'.$date->format(' M j, Y').'</td>';
              }
              //echo '<td>'.$data_meta_submission_nome.' '.$data_meta_submission_cognome.'</td>';
              echo '<td>'.htmlspecialchars($data_meta_submission_email).'</td>';
              echo '<td>';
              //referrer
              echo '<a href="'.htmlspecialchars($data_meta_submission_ref, ENT_QUOTES).'" title="" target="_blank">'.htmlspecialchars($data_meta_submission_ref, ENT_QUOTES).'</a>';
              echo '</td>';
              echo '<td>';
              echo '<a href="'.htmlspecialchars(get_bloginfo('url').$data_meta_submission_uri, ENT_QUOTES).'" title="" target="_blank">'.htmlspecialchars($data_meta_submission_uri, ENT_QUOTES).'</a>';
              echo '</td>';
              //echo '<td>valido/non valido/ecc..</td>';
              echo '<td><a href="?page=accua_forms_submissions_list&sid='.$row->afs_id.'" class="">Review</a>'; ?>
              <div id="delete" class="submitbox">
                <form id="delete-sub" method="post" action="">
                  <input type="hidden" name="del_sub_form" value="<?php echo $row->afs_id; ?>" />
                  <?php wp_nonce_field("del_sub_form_{$row->afs_id}", "_wpnonce_del_sub_form"); ?>
                  <input type="submit" id="delete-sub-data" class="accua_forms_trash" value="<?php _e('Trash', 'contact-forms') ?>" onclick='return window.confirm("Are you sure you want to trash this submission?");'/>
                </form>
              </div>
              <?php echo '</td>';
              echo '</tr>';
            } ?>
          </table>
        <?php } ?>
      </div>

      <?php if ($num_submissions) { ?>
      <div class="postbox ">
        <h2><span><?php _e('Total forms submitted', 'contact-forms'); ?></span></h2>
        <table>
          <tr class="first"><td class="first b"><?php echo $num_forms; ?></td><td class="t"><?php _e('Forms', 'contact-forms'); ?></td></tr>
          <tr class="first"><td class="first b"><?php echo $num_posts; ?></td><td class="t"><?php _e('Pages', 'contact-forms'); ?></td></tr>
          <tr class="first"><td class="first b"><?php echo $num_submissions; ?></td><td class="t"><?php _e('Submissions', 'contact-forms'); ?></td></tr>
          <tr class="first"><td class="first b"><?php echo $num_emails; ?></td><td class="t"><?php _e('Distinct Emails', 'contact-forms'); ?></td></tr>
        </table>
        <h2><span><?php _e('Submissions per lead status', 'contact-forms'); ?></span></h2>
        <table>
          <?php
          $lead_statuses = accua_forms_get_lead_statuses();
          foreach ($lead_statuses as $k => $v) {
            if (isset($num_submissions_per_lead_status[$k])) {
              echo '<tr class="first"><td class="first b">'.$num_submissions_per_lead_status[$k]->n.'</td><td class="t">'.htmlspecialchars($v).'</td></tr>';
            }
          }
          ?>
        </table>
      </div>
      <?php } ?>

      <span id="accua-forms-version">by Cimatti - <a href="https://www.cimatti.it/en/wordpress-plugins/contact-forms/">www.cimatti.it/en/wordpress-plugins/contact-forms</a>
        <br /><?php
        $plugin_data = get_plugin_data( ACCUA_FORMS_FILE );
        _e('Version', 'contact-forms'); echo " ".$plugin_data['Version'];
        ?></span>
    </div>
  <?php
}