<?php
function accua_forms_single_submission_movetotrash($id_sub){
	if(accua_forms_trash_submission($id_sub)){
      echo "<span class='savedsettings'>".__('Moved 1 submission to trash.', 'contact-forms')."</span>";
	} else{
      echo "<span class='error_savedsettings'>".__('Error moving submissions to trash.', 'contact-forms')."</span>";
	}
}

function accua_forms_add_submission_note($id_sub, $note_text){
	global $wpdb;
	$time = time();
	$current_date = gmdate('Y-m-d H:i:s', $time);

	$current_user = wp_get_current_user();
	$current_user_email = esc_html( $current_user->user_email );

	$query_add_note = "INSERT INTO {$wpdb->prefix}accua_forms_submissions_notes (afsn_sub_id, afsn_date, afsn_text, afsn_user) VALUES (%d, %s, %s, %s)";

	if( $wpdb->query($wpdb->prepare($query_add_note, $id_sub, $current_date, htmlspecialchars($note_text, ENT_QUOTES), $current_user_email)) === FALSE){
		echo "<span class='error_savedsettings'>Error saving note, please retry</span>";
	} else{
		echo "<span class='savedsettings'>Note added successfully</span>";
	}
}
function accua_forms_count_submission_note($id_sub)
{
	global $wpdb;
	$query_data_submission_notes = "SELECT DISTINCT COUNT(afsn_sub_id) 
        FROM `{$wpdb->prefix}accua_forms_submissions_notes`
        WHERE `afsn_sub_id` = %d";
	$count_submissions_form = $wpdb->get_var( $wpdb->prepare($query_data_submission_notes, $id_sub));
	return $count_submissions_form;
}
function accua_forms_return_submission_note($id_sub){
	global $wpdb;
	$query_data_submission_notes = "SELECT *
        FROM `{$wpdb->prefix}accua_forms_submissions_notes`
        WHERE `afsn_sub_id` = %d
        ORDER BY `afsn_date` DESC";

	$query_submission_notes = $wpdb->get_results($wpdb->prepare($query_data_submission_notes, $id_sub));

	foreach ($query_submission_notes as $value) {
		echo '<div class="note system-note">
			<div class="note_content">' . htmlspecialchars($value->afsn_text, ENT_QUOTES, null, false) . '</div>
			<p class="meta">
				<span class="note-date">' . $value->afsn_date . '</span> &middot; <span>'. $value->afsn_user.'</span> 
			</p>
			<form id="delete-note" method="post" action="">
				    <input type="hidden" name="del_note_sid" value="'.$value->afsn_sub_id.'" />
				    <input type="hidden" name="del_note_date" value="'.$value->afsn_date.'" />'; ?>
                    <?php wp_nonce_field("submission_{$value->afsn_sub_id}_note_del", "_wpnonce_note_del"); ?>
			<input type="submit" id="delete-note" class="submitdelete deletion" value="<?php _e('Delete note', 'contact-forms'); ?>" onclick="return window.confirm('Are you sure you want to trash this note?');"/>
			<?php echo '</form>
		</div>';
	}
}

function accua_forms_delete_submission_note($sid, $date){
	global $wpdb;
	$query_delete_notes = "DELETE FROM `{$wpdb->prefix}accua_forms_submissions_notes` WHERE `afsn_sub_id` = %d AND `afsn_date` = %s";
	if($wpdb->query($wpdb->prepare($query_delete_notes, $sid, $date)) === FALSE){
		echo "<span class='error_savedsettings'>Error delete note, please retry</span>";
	}
	else{
		echo "<span class='savedsettings'>".__("Note deleted", 'contact-forms')."</span>";
	}
}


function accua_forms_single_submission($head = false)
{
    wp_enqueue_style('accua',  plugins_url('accua.css', ACCUA_FORMS_FILE ), array(), ACCUA_FORMS_CSS_VERSION);
    wp_enqueue_script('accua-forms-set-lead-status', plugins_url('/js/accua-forms-set-lead-status.js', ACCUA_FORMS_FILE ), array('jquery', 'wp-pointer'), ACCUA_FORMS_JS_VERSION);
    global $wpdb;
    if (isset($_GET['sid'])) {
        $sid = (int) $_GET['sid'];
    }

    if ($head === true) {
        return;
    }
	$post = stripslashes_deep($_POST);
		if(isset($post['note_site_user']) && isset($post['note_id_sub'])){
			$note_site_user = $post['note_site_user'];
			$id_sub = $post['note_id_sub'];
            check_admin_referer("submission_{$id_sub}_note_add", "_wpnonce_note_add");
			accua_forms_add_submission_note($id_sub, $note_site_user);
		}
		if(isset($post['del_note_sid'])){
            check_admin_referer("submission_{$post['del_note_sid']}_note_del", "_wpnonce_note_del");
			accua_forms_delete_submission_note($post['del_note_sid'], $post['del_note_date']);
		}
		if($sid){
          $form_status = $wpdb->get_var($wpdb->prepare("SELECT `afs_status` FROM `{$wpdb->prefix}accua_forms_submissions` WHERE `afs_id` = %d", $sid));

          if(isset($post['del_sub_form'])) {
            $id = (int)$post['del_sub_form'];
            check_admin_referer("del_sub_form_{$id}", "_wpnonce_sub_del");
            accua_forms_single_submission_movetotrash($id);
          } ?>
		    <div id="accua_forms_submissions_list_page" class="accua_forms_admin_page wrap">
		        <h2 style="margin-bottom: 20px;" ><?php _e('Single form submission', 'contact-forms') ?></h2>
                <?php if($form_status >= 0){ /* ?>
		        <div>
		            <form method="get">
		                Status
		                <select>
		                    <option>valido</option>
		                    <option>SPAM - non valido - elimina senza anonimizzare il dato??</option>
		                    <option>riposta data</option>
		                    <option>richiesta completata - cancella il dato</option>
		                    <option>richiesta completata - mantieni il dato</option>
		                </select>
		                <input type="submit" class="button button-secondary" value="<?php _e('Apply', 'contact-forms') ?>" />
		            </form>
		        </div>
                */ ?>

			    <div id="delete" class="submitbox">
				    <form style="margin-top: 20px;" id="delete-sub" method="post" action="">
					    <input type="hidden" name="del_sub_form" value="<?php echo $sid; ?>" />
                      <?php wp_nonce_field("del_sub_form_{$sid}", "_wpnonce_sub_del"); ?>
					    <input type="submit" id="delete-sub-data" class="submitdelete deletion" value="<?php _e('Move to trash', 'contact-forms'); _e('(for spam or tests submissions)', 'contact-forms'); ?>" onclick='return window.confirm("Are you sure you want to trash this submission?");'/>
				    </form>
			    </div>
			    <br>
		        <?php }
			    //todo: email subito in alto
		        $query_data_submission = "SELECT *
		        FROM `{$wpdb->prefix}accua_forms_submissions`
		        WHERE `afs_id` = %d";

		        $query_data_submission_value = "SELECT *
		        FROM `{$wpdb->prefix}accua_forms_submissions_values`
		        WHERE `afsv_sub_id` = %d";

		        $data_submission = $wpdb->get_row($wpdb->prepare($query_data_submission, $sid));
		        $data_submission_values = $wpdb->get_results($wpdb->prepare($query_data_submission_value, $sid)); ?>
			    <div>
				    <?php
					/*$first_sub = $wpdb->get_var("SELECT `afs_id` FROM `{$wpdb->prefix}accua_forms_submissions` ORDER BY `afs_id` ASC LIMIT 1");
					$last_sub = $wpdb->get_var("SELECT `afs_id` FROM `{$wpdb->prefix}accua_forms_submissions` ORDER BY `afs_id` DESC LIMIT 1"); */
					$prev = $wpdb->get_var($wpdb->prepare("SELECT `afs_id` FROM `{$wpdb->prefix}accua_forms_submissions` WHERE `afs_status` >= 0 AND `afs_id` < %d ORDER BY `afs_id` DESC LIMIT 1", $sid));
					$next = $wpdb->get_var($wpdb->prepare("SELECT `afs_id` FROM `{$wpdb->prefix}accua_forms_submissions` WHERE `afs_status` >= 0 AND `afs_id` > %d ORDER BY `afs_id` ASC LIMIT 1", $sid));


					//se non ci sono precedenti (è la prima)
				    if($prev == ''){
				        echo '<span class="tablenav-pages-navspan button disabled" aria-hidden="true" style="margin-right: 10px;">‹ '.__('Previous submission', 'contact-forms').'</span>';
				    }else{
					    echo '<a class="prev-sub button" href="?page=accua_forms_submissions_list&sid='.htmlspecialchars($prev,ENT_QUOTES).'" style="margin-right: 10px;"><span class="screen-reader-text">'.__('Previous submission', 'contact-forms').'</span><span aria-hidden="true">‹ '.__('Previous submission', 'contact-forms').'</span></a>';
				    }

					//se non ci sono successivi (ultima)
				    if($next == ''){
					    echo '<span class="tablenav-pages-navspan button disabled" aria-hidden="true" style="margin-right: 10px;">'.__('Next submission', 'contact-forms').' ›</span>';
				    }else{
					    echo '<a class="prev-sub button" href="?page=accua_forms_submissions_list&sid='.htmlspecialchars($next,ENT_QUOTES).'" style="margin-right: 10px;"><span class="screen-reader-text">'.__('Next submission', 'contact-forms').'</span><span aria-hidden="true">'.__('Next submission', 'contact-forms').' ›</span></a>';
				    }
				    ?>
					<a href="?page=accua_forms_submissions_list" title="" style="margin-top: 6px; display: inline-block;"><?php _e('All submissions', 'contact-forms'); ?></a>
			    </div>
             <?php if(!isset($post['del_sub_form']) && $form_status == 0) { ?>
		     <div class="metabox-holder grid-3">
		        <div class="col1">
			        <div class="postbox ">
			            <h2 class="hndle"><?php _e('Stats', 'contact-forms'); ?></h2>
			            <table class="table_submit">
			                <tr>
			                    <td><strong><?php _e('Submission ID', 'contact-forms'); ?></strong></td>
			                    <td><?php echo htmlspecialchars($data_submission->afs_id); ?></td>
			                </tr>
			                <tr>
			                    <td><strong><?php _e('Form ID', 'contact-forms'); ?></strong></td>
			                    <td><?php echo htmlspecialchars($data_submission->afs_form_id); ?></td>
			                </tr>
			                <tr>
			                    <td><strong><?php _e('IP', 'contact-forms'); ?></strong></td>
			                    <td><?php echo htmlspecialchars($data_submission->afs_ip); ?></td>
			                </tr>
			                <tr>
			                    <td><strong><?php _e('URI', 'contact-forms'); ?></strong></td>
			                    <td><?php echo htmlspecialchars($data_submission->afs_uri); ?></td>
			                </tr>
			                <tr>
			                    <td><strong><?php _e('Referrer', 'contact-forms'); ?></strong></td>
			                    <td><?php echo htmlspecialchars($data_submission->afs_referrer); ?></td>
			                </tr>
			                <tr>
			                    <td><strong><?php _e('Language', 'contact-forms'); ?></strong></td>
			                    <td><?php echo htmlspecialchars($data_submission->afs_lang); ?></td>
			                </tr>
			                <tr>
			                    <td><strong><?php _e('Created', 'contact-forms'); ?></strong></td>
			                    <td><?php echo htmlspecialchars($data_submission->afs_created); ?></td>
			                </tr>
			                <tr>
			                    <td><strong><?php _e('Submitted', 'contact-forms'); ?></strong></td>
			                    <td><?php echo htmlspecialchars($data_submission->afs_submitted); ?></td>
			                </tr>
			                <tr>
			                    <td><strong><?php _e('Status', 'contact-forms'); ?></strong></td>
			                    <td><?php echo htmlspecialchars($data_submission->afs_status); ?></td>
			                    <?php /* todo: spiegare? */ ?>
			                </tr>
			            </table>
			        </div>
		        </div>

			     <div class="col2">
			         <div class="postbox ">
			             <h2 class="hndle"><?php _e('Submitted fields', 'contact-forms'); ?></h2>
			             <table class="table_submit">
			             <?php foreach ($data_submission_values as $value){
                             $san = htmlspecialchars($value->afsv_value);
			                 echo '<tr>';
			                 echo '<td><strong>'.$value->afsv_field_id.'</strong></td><td>'.$san .'</td>';
			                 echo '</tr>';
			             } ?>
			             </table>
			         </div>
		         </div>
			     <div class="col3">
				     <div class="postbox ">
					     <div>
                             <h3><?php _e('Lead status', 'contact-forms'); ?></h3>
                             <p><?php echo accua_forms_select_lead_status($data_submission->afs_id, $data_submission->afs_lead_status); ?></p>
						     <h3><?php _e('Add note', 'contact-forms'); ?></h3>
						     <form action=""  id="editnote" method="post" action="">
							     <textarea id="note_site_user" name="note_site_user" rows="4" style="width: 100%; margin-bottom:5px" placeholder="<?php _e('Insert your note here...', 'contact-forms'); ?>"></textarea>
							     <input type="hidden" name="note_id_sub" value="<?php echo $sid; ?>" />
                                 <?php wp_nonce_field("submission_{$sid}_note_add", "_wpnonce_note_add"); ?>
							     <input type="submit" class="button button-secondary" value="<?php _e( 'Save', 'contact-forms'); ?>" /></p>
						     </form>
					     </div>
					     <div>
						     <?php $conta_note = accua_forms_count_submission_note($sid);
							     if($conta_note == 0){
								     echo '<p>' . __('No notes for this submissions', 'contact-forms') . '</p>';
							     } else{
								     echo '<h2>' . __('Existing notes', 'contact-forms') . '</h2>';
								     accua_forms_return_submission_note($sid);
							     }
						     ?>
					     </div>
				     </div>
			     </div>
		    </div>

            <?php } ?>
		    </div>
		    <?php
		}
  if (!class_exists('AccuaFormsHelp')) {
    require_once('accua-forms-help.php');
  }
  $accuaHelp = AccuaFormsHelp::getInstance();
  $accuaHelp->finished();
}