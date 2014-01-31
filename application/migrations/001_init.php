<?php

class Migration_Init extends CI_Migration {
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

#        $this->dbforge->create_table('users', TRUE);
#        $this->dbforge->create_table('users_groups', TRUE);
#        $this->dbforge->create_table('variant_count_0', TRUE);
#        $this->dbforge->create_table('variations_0', TRUE);
#        $this->dbforge->create_table('variations_queue_0', TRUE);
#        $this->dbforge->create_table('versions', TRUE);
  }
 
  public function down(){
    $this->dbforge->drop_table('login_attempts', TRUE);
    $this->dbforge->drop_table('reviews_0', TRUE);
    $this->dbforge->drop_table('users', TRUE);
    $this->dbforge->drop_table('users_groups', TRUE);
    $this->dbforge->drop_table('variant_count_0', TRUE);
    $this->dbforge->drop_table('variations_0', TRUE);
    $this->dbforge->drop_table('variations_queue_0', TRUE);
    $this->dbforge->drop_table('versions', TRUE);
  }
}
