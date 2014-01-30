<?php

class Migration_Init extends CI_Migration {
    public function up(){
#        $this->dbforge->add_field("id int(11) unsigned NOT NULL AUTO_INCREMENT");
#        $this->dbforge->add_field("email varchar(255) NOT NULL DEFAULT ''");
#        $this->dbforge->add_field("password varchar(255) NOT NULL DEFAULT ''");
 
#        $this->dbforge->add_key('id', TRUE);
#        $this->dbforge->add_key('email');
        
        $this->dbforge->create_table('groups', TRUE);

#        $this->dbforge->create_table('login_attempts', TRUE);
#        $this->dbforge->create_table('reviews_0', TRUE);
#        $this->dbforge->create_table('users', TRUE);
#        $this->dbforge->create_table('users_groups', TRUE);
#        $this->dbforge->create_table('variant_count_0', TRUE);
#        $this->dbforge->create_table('variations_0', TRUE);
#        $this->dbforge->create_table('variations_queue_0', TRUE);
#        $this->dbforge->create_table('versions', TRUE);
    }
 
    public function down(){
        $this->dbforge->drop_table('users');
    }
}
