<?php
// Usuario.php
/**
 * Model de Usuario
 * @filesource  07/03/2010
 * @package     <<application>>
 * @subpackage  <<application>>.application.models
 * @version     $Id: Usuario.php 187 2012-03-05 22:35:49Z elinis.matos@gmail.com $
 */
class Usuario extends Phpdf_Db_Table
{
    const K_PERFIL_PARTICIPANTE = 4;
    const COLABORADOR = 'S';

    protected $_name = 'usuario';
    protected $_dependentTables  = array( 'Atividade', 'Inscricao', 'Presenca' );
    protected $_primary = array('id');

    protected $_referenceMap = array(
        'Perfil' => array(
            'columns'       => 'perfil_id',
            'refTableClass' => 'Perfil',
            'refColumns'    => 'id',
        ),
        'Uf' => array(
            'columns'       => 'uf_id',
            'refTableClass' => 'Uf',
            'refColumns'    => 'id',
        )
    );

    /**
     * Verifica se existe o email passado no banco
     * @param string $email
     * @return boolean
     */
    public function hasEmail($email) {
        // Faz o tratamento para que trate os caracteres de escape
        $email = $this->getAdapter()->quote($email, 'string');

        if($this->fetchRow('email = '. $email)) {
            return true;
        }
        return false;
    }

    /**
     * Faz o login caso o email e senha estejam corretos
     * @param string $email
     * @param string $password
     * @return boolean
     */
    public function login($email, $password)
    {
        $authAdapter = new Zend_Auth_Adapter_DbTable(
            Zend_Db_Table::getDefaultAdapter(),
            'usuario',
            'email',
            'senha',
            'md5(?)'
        );

        $authAdapter->setIdentity($email)
                    ->setCredential($password);
        $auth   = Zend_Auth::getInstance();
        $result = $auth->authenticate($authAdapter);
        if($auth->hasIdentity($authAdapter)) {
            $oIdentify        = $authAdapter->getResultRowObject(null, 'senha');
            // @FIXME Gravar registros em cache
            $codigoPerfil = Perfil::build()->find($oIdentify->perfil_id)
                                           ->current()
                                           ->codigo;
            $oIdentify->sRole = $codigoPerfil;
            $auth->getStorage()->write($oIdentify);
            return true;
        }
        return false;
    }

    /**
     *  Retorna os usuários que são palestrantes
     *  @return Zend_Db_Table_RowSet
     */
    public function findPalestrante()
    {
        $select = $this->select();
        $select->from(array('u' => 'usuario'), 
                    array( 
                        'nome_palestrante' => 'nome'  ,
                        'email',  
                        'cpf'  
                        )
                )
               ->setIntegrityCheck(false)
               ->joinInner(array('a' => 'atividade'), 'u.id = a.id_palestrante', array(
                   'nome_atividade' => 'nome',
                   'descricao',
                   'qt_horas'
               ))
                ->order( 'u.nome' )
                ->where( 'a.situacao = 2' );
        return $this->fetchAll($select);
    }
    
    public function sortParticipante()
    {
        $select = $this->select();
        $select->from(array('p' => 'presenca'), 
                    array( 
                        'id' => 'id',
                        'usuario_id'
                        )
                )
               ->setIntegrityCheck(false)
               ->joinInner(array('u' => 'usuario'), 'u.id = p.usuario_id', array(
                   'nome' => 'nome',
                   'email',
                   'cpf',
                   'cidade'
               ))
                ->order( 'rand()' )
                ->where( 'p.data_primeira_presenca is not null and data_segunda_presenca is not null and sorteado = 0' )
                ->limit( '10' );
        return $this->fetchAll($select);
    }
    
    public function sorteadosParticipante()
    {
        $select = $this->select();
        $select->from(array('p' => 'presenca'), 
                    array( 
                        'id' => 'id',
                        'usuario_id',
                        'premio'
                        )
                )
               ->setIntegrityCheck(false)
               ->joinInner(array('u' => 'usuario'), 'u.id = p.usuario_id', array(
                   'nome' => 'nome',
                   'email'
               ))
                ->where( 'sorteado = 1' );
                return $this->fetchAll($select);
    }


    public function getInfoUsuario($id)
    {
        $select = $this->select();
        $select->from(array('u' => 'usuario'))
               ->setIntegrityCheck(false)
               ->where('u.id = ?', $id);
        return $this->fetchRow($select);
    }

   /**
     * Faz o logout do usuario
     * @return boolean
     */
    public function logout()
    {
        Zend_Auth::getInstance()->clearIdentity();
    }

    /**
     * Retorna a instância deste objeto
     */
    public static function build()
    {
        return new self();
    }

    /**
     * @param string|null $senha
     * @param int $length
     * @return string
     */
    public function gerarSenha($senha = null, $length = 6)
    {
        if (null !== $senha) {
            return md5($senha);
        }
        /**
         * @todo gerar senha randomica
         */
    }

}
