<?php

class Migration_Init extends CI_Migration {
  /**
   * Up
   *
   * This will create the following tables:
   *   - groups
   *   - login_attempts
   *   - migrations
   *   - reviews_0
   *   - users
   *   - users_groups
   *   - variant_count_0
   *   - variations_0
   *   - variations_queue_0
   *   - versions
   *
   * @author Sean Ephraim
   * @access public
   */
  public function up(){
    # Groups table
    $fields = array(
      'id' => array(
          'type' => 'INT',
          'constraint' => 8, 
          'unsigned' => TRUE,
          'null' => FALSE,
          'auto_increment' => TRUE
      ),
      'name' => array(
          'type' => 'VARCHAR',
          'constraint' => 20, 
          'null' => FALSE,
      ),
      'description' => array(
          'type' => 'VARCHAR',
          'constraint' => 100, 
          'null' => FALSE,
      ),
    );
    $this->dbforge->add_field($fields);
    $this->dbforge->add_key('id', TRUE);
    $this->dbforge->create_table('groups', TRUE);
    # Insert core groups
    $data = array(
      array(
        'name' => 'admin',
        'description' => 'Administrator',
      ),
      array(
        'name' => 'members',
        'description' => 'General User',
      )
    );
    $this->db->insert_batch('groups', $data); 

    # Login attempts table
    $fields = array(
      'id' => array(
          'type' => 'INT',
          'constraint' => 8, 
          'unsigned' => TRUE,
          'null' => FALSE,
          'auto_increment' => TRUE
      ),
      'ip_address' => array(
          'type' => 'VARBINARY',
          'constraint' => 16, 
          'null' => FALSE,
      ),
      'login' => array(
          'type' => 'VARCHAR',
          'constraint' => 100, 
          'null' => FALSE,
      ),
      'time' => array(
          'type' => 'INT',
          'constraint' => 100, 
          'unsigned' => TRUE,
          'null' => TRUE,
      ),
    );
    $this->dbforge->add_field($fields);
    $this->dbforge->add_key('id', TRUE);
    $this->dbforge->create_table('login_attempts', TRUE);

    # Reviews table
    $fields = array(
      'id' => array(
          'type' => 'INT',
          'constraint' => 11, 
          'unsigned' => TRUE,
          'null' => FALSE,
          'auto_increment' => TRUE
      ),
      'variant_id' => array(
          'type' => 'INT',
          'constraint' => 11, 
          'unsigned' => TRUE,
          'null' => FALSE,
      ),
      'variation' => array(
          'type' => 'VARCHAR',
          'constraint' => 100, 
          'null' => TRUE,
      ),
      'created' => array(
          'type' => 'DATETIME',
          'null' => TRUE,
      ),
      'updated' => array(
          'type' => 'DATETIME',
          'null' => TRUE,
      ),
      'confirmed_for_release' => array(
          'type' => 'TINYINT',
          'constraint' => 1, 
          'null' => FALSE,
          'default' => 0,
      ),
      'scheduled_for_deletion' => array(
          'type' => 'TINYINT',
          'constraint' => 1, 
          'null' => FALSE,
          'default' => 0,
      ),
      'informatics_comments' => array(
          'type' => 'LONGTEXT',
          'null' => FALSE,
      ),
    );
    $this->dbforge->add_field($fields);
    $this->dbforge->add_key('id', TRUE);
    $this->dbforge->add_key('variant_id');
    $this->dbforge->create_table('reviews_0', TRUE);

    # Users table
    $fields = array(
      'id' => array(
          'type' => 'INT',
          'constraint' => 8, 
          'unsigned' => TRUE,
          'null' => FALSE,
          'auto_increment' => TRUE
      ),
      'ip_address' => array(
          'type' => 'VARBINARY',
          'constraint' => 16, 
          'null' => FALSE,
      ),
      'username' => array(
          'type' => 'VARCHAR',
          'constraint' => 100, 
          'null' => FALSE,
      ),
      'password' => array(
          'type' => 'VARCHAR',
          'constraint' => 80, 
          'null' => FALSE,
      ),
      'salt' => array(
          'type' => 'VARCHAR',
          'constraint' => 40, 
          'null' => TRUE,
      ),
      'email' => array(
          'type' => 'VARCHAR',
          'constraint' => 100, 
          'null' => FALSE,
      ),
      'activation_code' => array(
          'type' => 'VARCHAR',
          'constraint' => 40, 
          'null' => TRUE,
      ),
      'forgotten_password_code' => array(
          'type' => 'VARCHAR',
          'constraint' => 40, 
          'null' => TRUE,
      ),
      'forgotten_password_time' => array(
          'type' => 'INT',
          'constraint' => 11, 
          'unsigned' => TRUE,
          'null' => TRUE,
      ),
      'remember_code' => array(
          'type' => 'VARCHAR',
          'constraint' => 40, 
          'null' => TRUE,
      ),
      'created_on' => array(
          'type' => 'INT',
          'constraint' => 11, 
          'unsigned' => TRUE,
          'null' => FALSE,
      ),
      'last_login' => array(
          'type' => 'INT',
          'constraint' => 11, 
          'unsigned' => TRUE,
          'null' => TRUE,
      ),
      'active' => array(
          'type' => 'TINYINT',
          'constraint' => 1, 
          'unsigned' => TRUE,
          'null' => TRUE,
      ),
      'first_name' => array(
          'type' => 'VARCHAR',
          'constraint' => 50, 
          'null' => TRUE,
      ),
      'last_name' => array(
          'type' => 'VARCHAR',
          'constraint' => 50, 
          'null' => TRUE,
      ),
      'company' => array(
          'type' => 'VARCHAR',
          'constraint' => 100, 
          'null' => TRUE,
      ),
      'phone' => array(
          'type' => 'VARCHAR',
          'constraint' => 20, 
          'null' => TRUE,
      ),
    );
    $this->dbforge->add_field($fields);
    $this->dbforge->add_key('id', TRUE);
    $this->dbforge->create_table('users', TRUE);
    # Insert admin user
    $data = array(
      'username' => 'admin',
      'password' => '$2a$08$lADIGrpu0qE8TFo9zj0gKO5xEQktINAAwYS7Fs1JKtMqGGnB9GeXe',
      'email' => 'admin@admin.com',
      'created_on' => '1268889823',
      'last_login' => '1268889823',
      'active' => 1,
      'first_name' => 'Admin',
    );
    $this->db->insert('users', $data); 

    # Users groups table
    $fields = array(
      'id' => array(
          'type' => 'INT',
          'constraint' => 8, 
          'unsigned' => TRUE,
          'null' => FALSE,
          'auto_increment' => TRUE
      ),
      'user_id' => array(
          'type' => 'INT',
          'constraint' => 8, 
          'unsigned' => TRUE,
          'null' => FALSE,
      ),
      'group_id' => array(
          'type' => 'INT',
          'constraint' => 8, 
          'unsigned' => TRUE,
          'null' => FALSE,
      ),
    );
    $this->dbforge->add_field($fields);
    $this->dbforge->add_key('id', TRUE);
    $this->dbforge->add_key('user_id');
    $this->dbforge->add_key('group_id');
    $this->dbforge->create_table('users_groups', TRUE);
    # Insert admin's groups
    $data = array(
      array(
        'id' => 1,
        'user_id' => 1,
        'group_id' => 1,
      ),
      array(
        'id' => 2,
        'user_id' => 1,
        'group_id' => 2,
      ),
    );
    $this->db->insert_batch('users_groups', $data); 

    # Variant count table
    $fields = array(
      'id' => array(
          'type' => 'INT',
          'constraint' => 11, 
          'unsigned' => TRUE,
          'null' => FALSE,
          'auto_increment' => TRUE
      ),
      'gene' => array(
          'type' => 'CHAR',
          'constraint' => 10, 
          'null' => TRUE,
      ),
      'count' => array(
          'type' => 'INT',
          'constraint' => 11, 
          'null' => TRUE,
          'default' => 0,
      ),
    );
    $this->dbforge->add_field($fields);
    $this->dbforge->add_key('id', TRUE);
    $this->dbforge->create_table('variant_count_0', TRUE);

    # Variations table
    $fields = array(
      'id' => array(
          'type' => 'INT',
          'constraint' => 11, 
          'unsigned' => TRUE,
          'null' => FALSE,
          'auto_increment' => TRUE
      ),
      'variation' => array(
          'type' => 'VARCHAR',
          'constraint' => 100, 
          'null' => TRUE,
      ),
      'chr' => array(
          'type' => 'VARCHAR',
          'constraint' => 100, 
          'null' => TRUE,
      ),
      'pos' => array(
          'type' => 'VARCHAR',
          'constraint' => 100, 
          'null' => TRUE,
      ),
      'ref' => array(
          'type' => 'VARCHAR',
          'constraint' => 100, 
          'null' => TRUE,
      ),
      'alt' => array(
          'type' => 'VARCHAR',
          'constraint' => 100, 
          'null' => TRUE,
      ),
      'gene' => array(
          'type' => 'VARCHAR',
          'constraint' => 10, 
          'null' => TRUE,
      ),
      'hgvs_protein_change' => array(
          'type' => 'VARCHAR',
          'constraint' => 100, 
          'null' => TRUE,
      ),
      'hgvs_nucleotide_change' => array(
          'type' => 'VARCHAR',
          'constraint' => 100, 
          'null' => TRUE,
      ),
      'variantlocale' => array(
          'type' => 'VARCHAR',
          'constraint' => 100, 
          'null' => TRUE,
      ),
      'pathogenicity' => array(
          'type' => 'VARCHAR',
          'constraint' => 100, 
          'null' => TRUE,
      ),
      'disease' => array(
          'type' => 'VARCHAR',
          'constraint' => 100, 
          'null' => TRUE,
      ),
      'pubmed_id' => array(
          'type' => 'VARCHAR',
          'constraint' => 100, 
          'null' => TRUE,
      ),
      'dbsnp' => array(
          'type' => 'VARCHAR',
          'constraint' => 50, 
          'null' => TRUE,
      ),
      'summary_insilico' => array(
          'type' => 'INT',
          'constraint' => 11, 
          'null' => TRUE,
      ),
      'summary_frequency' => array(
          'type' => 'INT',
          'constraint' => 11, 
          'null' => TRUE,
      ),
      'summary_published' => array(
          'type' => 'INT',
          'constraint' => 11, 
          'null' => TRUE,
      ),
      'comments' => array(
          'type' => 'LONGTEXT',
          'null' => TRUE,
      ),
      'release_date' => array(
          'type' => 'DATETIME',
          'null' => FALSE,
      ),
      'lrt_omega' => array(
          'type' => 'FLOAT',
          'null' => TRUE,
      ),
      'phylop_score' => array(
          'type' => 'FLOAT',
          'null' => TRUE,
      ),
      'phylop_pred' => array(
          'type' => 'VARCHAR',
          'constraint' => 5,
          'null' => TRUE,
      ),
      'sift_score' => array(
          'type' => 'FLOAT',
          'null' => TRUE,
      ),
      'sift_pred' => array(
          'type' => 'VARCHAR',
          'constraint' => 5,
          'null' => TRUE,
      ),
      'polyphen2_score' => array(
          'type' => 'VARCHAR',
          'constraint' => 100,
          'null' => TRUE,
      ),
      'polyphen2_pred' => array(
          'type' => 'VARCHAR',
          'constraint' => 100,
          'null' => TRUE,
      ),
      'lrt_score' => array(
          'type' => 'FLOAT',
          'null' => TRUE,
      ),
      'lrt_pred' => array(
          'type' => 'VARCHAR',
          'constraint' => 5,
          'null' => TRUE,
      ),
      'mutationtaster_score' => array(
          'type' => 'FLOAT',
          'null' => TRUE,
      ),
      'mutationtaster_pred' => array(
          'type' => 'VARCHAR',
          'constraint' => 5,
          'null' => TRUE,
      ),
      'gerp_nr' => array(
          'type' => 'FLOAT',
          'null' => TRUE,
      ),
      'gerp_rs' => array(
          'type' => 'FLOAT',
          'null' => TRUE,
      ),
      'gerp_pred' => array(
          'type' => 'VARCHAR',
          'constraint' => 5,
          'null' => TRUE,
      ),
      'evs_ea_ac' => array(
          'type' => 'INT',
          'constraint' => 11,
          'null' => TRUE,
      ),
      'evs_ea_an' => array(
          'type' => 'INT',
          'constraint' => 11,
          'null' => TRUE,
      ),
      'evs_ea_af' => array(
          'type' => 'INT',
          'constraint' => 11,
          'null' => TRUE,
      ),
      'evs_aa_ac' => array(
          'type' => 'INT',
          'constraint' => 11,
          'null' => TRUE,
      ),
      'evs_aa_an' => array(
          'type' => 'INT',
          'constraint' => 11,
          'null' => TRUE,
      ),
      'evs_aa_af' => array(
          'type' => 'INT',
          'constraint' => 11,
          'null' => TRUE,
      ),
      'evs_all_af' => array(
          'type' => 'INT',
          'constraint' => 11,
          'null' => TRUE,
      ),
      'otoscope_ac' => array(
          'type' => 'INT',
          'constraint' => 11,
          'null' => TRUE,
      ),
      'otoscope_an' => array(
          'type' => 'INT',
          'constraint' => 11,
          'null' => TRUE,
      ),
      'tg_acb_ac' => array(
          'type' => 'INT',
          'constraint' => 11,
          'null' => TRUE,
      ),
      'tg_acb_an' => array(
          'type' => 'INT',
          'constraint' => 11,
          'null' => TRUE,
      ),
      'tg_afr_af' => array(
          'type' => 'INT',
          'constraint' => 11,
          'null' => TRUE,
      ),
      'tg_amr_af' => array(
          'type' => 'INT',
          'constraint' => 11,
          'null' => TRUE,
      ),
      'tg_asn_af' => array(
          'type' => 'INT',
          'constraint' => 11,
          'null' => TRUE,
      ),
      'tg_asw_ac' => array(
          'type' => 'INT',
          'constraint' => 11,
          'null' => TRUE,
      ),
      'tg_asw_an' => array(
          'type' => 'INT',
          'constraint' => 11,
          'null' => TRUE,
      ),
      'tg_cdx_ac' => array(
          'type' => 'INT',
          'constraint' => 11,
          'null' => TRUE,
      ),
      'tg_cdx_an' => array(
          'type' => 'INT',
          'constraint' => 11,
          'null' => TRUE,
      ),
      'tg_ceu_ac' => array(
          'type' => 'INT',
          'constraint' => 11,
          'null' => TRUE,
      ),
      'tg_ceu_an' => array(
          'type' => 'INT',
          'constraint' => 11,
          'null' => TRUE,
      ),
      'tg_chb_ac' => array(
          'type' => 'INT',
          'constraint' => 11,
          'null' => TRUE,
      ),
      'tg_chb_an' => array(
          'type' => 'INT',
          'constraint' => 11,
          'null' => TRUE,
      ),
      'tg_chs_ac' => array(
          'type' => 'INT',
          'constraint' => 11,
          'null' => TRUE,
      ),
      'tg_chs_an' => array(
          'type' => 'INT',
          'constraint' => 11,
          'null' => TRUE,
      ),
      'tg_clm_ac' => array(
          'type' => 'INT',
          'constraint' => 11,
          'null' => TRUE,
      ),
      'tg_clm_an' => array(
          'type' => 'INT',
          'constraint' => 11,
          'null' => TRUE,
      ),
      'tg_eur_af' => array(
          'type' => 'INT',
          'constraint' => 11,
          'null' => TRUE,
      ),
      'tg_fin_ac' => array(
          'type' => 'INT',
          'constraint' => 11,
          'null' => TRUE,
      ),
      'tg_fin_an' => array(
          'type' => 'INT',
          'constraint' => 11,
          'null' => TRUE,
      ),
      'tg_gbr_ac' => array(
          'type' => 'INT',
          'constraint' => 11,
          'null' => TRUE,
      ),
      'tg_gbr_an' => array(
          'type' => 'INT',
          'constraint' => 11,
          'null' => TRUE,
      ),
      'tg_gih_ac' => array(
          'type' => 'INT',
          'constraint' => 11,
          'null' => TRUE,
      ),
      'tg_gih_an' => array(
          'type' => 'INT',
          'constraint' => 11,
          'null' => TRUE,
      ),
      'tg_ibs_ac' => array(
          'type' => 'INT',
          'constraint' => 11,
          'null' => TRUE,
      ),
      'tg_ibs_an' => array(
          'type' => 'INT',
          'constraint' => 11,
          'null' => TRUE,
      ),
      'tg_jpt_ac' => array(
          'type' => 'INT',
          'constraint' => 11,
          'null' => TRUE,
      ),
      'tg_jpt_an' => array(
          'type' => 'INT',
          'constraint' => 11,
          'null' => TRUE,
      ),
      'tg_khv_ac' => array(
          'type' => 'INT',
          'constraint' => 11,
          'null' => TRUE,
      ),
      'tg_khv_an' => array(
          'type' => 'INT',
          'constraint' => 11,
          'null' => TRUE,
      ),
      'tg_lwk_ac' => array(
          'type' => 'INT',
          'constraint' => 11,
          'null' => TRUE,
      ),
      'tg_lwk_an' => array(
          'type' => 'INT',
          'constraint' => 11,
          'null' => TRUE,
      ),
      'tg_mxl_ac' => array(
          'type' => 'INT',
          'constraint' => 11,
          'null' => TRUE,
      ),
      'tg_mxl_an' => array(
          'type' => 'INT',
          'constraint' => 11,
          'null' => TRUE,
      ),
      'tg_pel_ac' => array(
          'type' => 'INT',
          'constraint' => 11,
          'null' => TRUE,
      ),
      'tg_pel_an' => array(
          'type' => 'INT',
          'constraint' => 11,
          'null' => TRUE,
      ),
      'tg_pur_ac' => array(
          'type' => 'INT',
          'constraint' => 11,
          'null' => TRUE,
      ),
      'tg_pur_an' => array(
          'type' => 'INT',
          'constraint' => 11,
          'null' => TRUE,
      ),
      'tg_tsi_ac' => array(
          'type' => 'INT',
          'constraint' => 11,
          'null' => TRUE,
      ),
      'tg_tsi_an' => array(
          'type' => 'INT',
          'constraint' => 11,
          'null' => TRUE,
      ),
      'tg_yri_ac' => array(
          'type' => 'INT',
          'constraint' => 11,
          'null' => TRUE,
      ),
      'tg_yri_an' => array(
          'type' => 'INT',
          'constraint' => 11,
          'null' => TRUE,
      ),
      'tg_all_af' => array(
          'type' => 'INT',
          'constraint' => 11,
          'null' => TRUE,
      ),
    );
    $this->dbforge->add_field($fields);
    $this->dbforge->add_key('id', TRUE);
    $this->dbforge->create_table('variations_0', TRUE);

    #create variaitons log
    $fields['id']['auto_increment'] = FALSE;
    $this->dbforge->add_field($fields);
    $this->dbforge->create_table('variations_log', TRUE);
    
    # Variations queue table
    # NOTE: This is the EXACT same structure as the variations_0
    #       table (described above) except that the 'id' field
    #       is not auto-incremented.
    $fields['id']['auto_increment'] = FALSE;
    $this->dbforge->add_field($fields);
    $this->dbforge->add_key('id', TRUE);
    $this->dbforge->create_table('variations_queue_0', TRUE);

    # Versions table
    $fields = array(
      'id' => array(
          'type' => 'INT',
          'constraint' => 11, 
          'unsigned' => TRUE,
          'null' => FALSE,
          'auto_increment' => TRUE
      ),
      'version' => array(
          'type' => 'VARCHAR',
          'constraint' => 10, 
          'null' => FALSE,
      ),
      'created' => array(
          'type' => 'DATETIME',
          'null' => FALSE,
      ),
      'updated' => array(
          'type' => 'DATETIME',
          'null' => FALSE,
      ),
      'variants' => array(
          'type' => 'INT',
          'constraint' => 11,
          'null' => TRUE,
      ),
      'genes' => array(
          'type' => 'INT',
          'constraint' => 11,
          'null' => TRUE,
      ),
    );
    $this->dbforge->add_field($fields);
    $this->dbforge->add_key('id', TRUE);
    $this->dbforge->create_table('versions', TRUE);
    # Insert initial version info
    $data = array(
      'id' => 0,
      'version' => 0,
      'created' => date("Y-m-d H:i:s"),
      'updated' => date("Y-m-d H:i:s"),
      'variants' => 0,
      'genes' => 0,
    );
    $this->db->insert('versions', $data); 
    
    #create expert curations table 
    $fields = array(
      'id' => array(
          'type' => 'INT',
          'constraint' => 11, 
          'unsigned' => TRUE,
          'null' => FALSE,
          'auto_increment' => TRUE
      ),
      'variation' => array(
          'type' => 'VARCHAR',
          'constraint' => 100, 
          'null' => TRUE,
      ),
      'chr' => array(
          'type' => 'VARCHAR',
          'constraint' => 100, 
          'null' => TRUE,
      ),
      'pos' => array(
          'type' => 'VARCHAR',
          'constraint' => 100, 
          'null' => TRUE,
      ),
      'ref' => array(
          'type' => 'VARCHAR',
          'constraint' => 100, 
          'null' => TRUE,
      ),
      'alt' => array(
          'type' => 'VARCHAR',
          'constraint' => 100, 
          'null' => TRUE,
      ),
      'gene' => array(
          'type' => 'VARCHAR',
          'constraint' => 10, 
          'null' => TRUE,
      ),
      'pathogenicity' => array(
          'type' => 'VARCHAR',
          'constraint' => 100, 
          'null' => TRUE,
      ),
      'disease' => array(
          'type' => 'VARCHAR',
          'constraint' => 100, 
          'null' => TRUE,
      ),
      'pubmed_id' => array(
          'type' => 'VARCHAR',
          'constraint' => 100, 
          'null' => TRUE,
      ),
      'date' => array(
          'type' => 'DATETIME',
          'null' => FALSE,
      ),
      'delete' => array(
          'type' => 'INT',
          'constraint' => 1, 
          'unsigned' => TRUE,
          'null' => TRUE,
      )
    );
    $this->dbforge->add_field($fields);
    $this->dbforge->add_key('id', TRUE);
    $this->dbforge->create_table('expert_curations', TRUE);
    //create expert log 
    $fields['id']['auto_increment'] = FALSE;
    $this->dbforge->add_field($fields);
    $this->dbforge->create_table('expert_log', TRUE);
  }
 
  /**
   * Down
   *
   * This will drop all the tables created with the
   * up() function.
   *
   * @author Sean Ephraim
   * @access public
   */
  public function down(){
    $this->dbforge->drop_table('login_attempts', TRUE);
    $this->dbforge->drop_table('reviews_0', TRUE);
    $this->dbforge->drop_table('users', TRUE);
    $this->dbforge->drop_table('users_groups', TRUE);
    $this->dbforge->drop_table('variant_count_0', TRUE);
    $this->dbforge->drop_table('variations_0', TRUE);
    $this->dbforge->drop_table('variations_queue_0', TRUE);
    $this->dbforge->drop_table('variations_log', TRUE);
    $this->dbforge->drop_table('expert_curations', TRUE);
    $this->dbforge->drop_table('expert_log', TRUE);
    $this->dbforge->drop_table('versions', TRUE);
  }
}
