<?php
// Presenca.php
/**
 * Model de Presenca
 * @filesource  07/03/2010
 * @package     <<application>>
 * @subpackage  <<application>>.application.models
 * @version     $Id: presenca.php 154 2010-06-11 13:28:38Z ramon.ornela $
 */
class Presenca extends Phpdf_Db_Table
{
    protected $_name = 'presenca';
    protected $_dependentTables  = array( 'presenca' );
    protected $_primary = array('id');

    /**
     * Retorna a instância deste objeto
     */
    public static function build()
    {
        return new self();
    }
}
