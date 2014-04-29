<?php
/**
 * Controle de inscrições
 * @filesource  07/03/2010
 * @author      PHPDF <http://www.phpdf.org.br>
 * @package     <<application>>
 * @subpackage  <<application>>.application.controllers
 * @version     $Id: InscricoesController.php 155 2010-06-14 17:50:23Z ramon.ornela $
 */
class PresencasController extends Phpdf_Controller_Action
{
    public function indexAction()
    {
    	if(!Zend_Auth::getInstance()->hasIdentity()) {
           $this->_redirect('participantes/login');
        }
    }

    public function pesquisaparticipanteAction()
    {
        if ($this->getRequest()->isPost()) {
        	$tbUsuario  = new Usuario();
            $rowUsuario = $tbUsuario->fetchRow(array('email = ?' => $this->_getParam('email')));
            $this->view->participante  = $rowUsuario;
		} else {
        	$tbUsuario  = new Usuario();
            $rowUsuario = $tbUsuario->fetchRow(array('id = ?' => $this->_getParam('code')));
            $this->view->participante  = $rowUsuario;
		}

            $tbPresenca = new Presenca();
            $rowPresenca = $tbPresenca->fetchRow(array('usuario_id = ?' => $rowUsuario->id));
            if (!$rowPresenca) {
            	$qualPresenca = 1;
            	$presenca = array();
            } else {
            	$qualPresenca = 2;
            	$presenca = $rowPresenca->toArray();
            }
            $this->view->qualpresenca = $qualPresenca;
            $this->view->presenca = $presenca;
    }

	public function codeparticipanteAction()
    {
        //if ($this->getRequest()->isPost()) {
	$encrypted = base64_decode( $this->_getParam('code'));

	$key = 'livre2013';
	$iv = '92832345';

	$cipher = mcrypt_module_open(MCRYPT_BLOWFISH,'','cbc','');

	mcrypt_generic_init($cipher, $key, $iv);
	$decrypted = mdecrypt_generic($cipher,$encrypted);
	mcrypt_generic_deinit($cipher);

	$usuario_id = $decrypted;
	$tbUsuario  = new Usuario();
           $rowUsuario = $tbUsuario->fetchRow(array('id = ?' =>$usuario_id ));
           $this->view->participante  = $rowUsuario;

            $tbPresenca = new Presenca();
            $rowPresenca = $tbPresenca->fetchRow(array('usuario_id = ?' => $rowUsuario->id));
            if (!$rowPresenca) {
            	$qualPresenca = 1;
            	$presenca = array();
            } else {
            	$qualPresenca = 2;
            	$presenca = $rowPresenca->toArray();
            }
            $this->view->qualpresenca = $qualPresenca;
            $this->view->presenca = $presenca;
       // }
    }

    public function gravapresencaAction() {
    	$id = $this->_getParam('id');
    	$idUsuario = $this->_getParam('usuario_id');
    	$qualPresenca = $this->_getParam('qualpresenca');
    	try {
    		if ($qualPresenca == 1) {
    			$oPresenca = Presenca::build();
    			$presenca = $oPresenca->createRow();
	    		$presenca->usuario_id   = $idUsuario;
    			$presenca->evento_id = 1;
    			$presenca->data_primeira_presenca  = date('Y-m-d H:i:s');
    		} else {
    			$oPresenca  = new Presenca();
            	$presenca = $oPresenca->fetchRow(array('id = ?' => $id));
            	$presenca->data_segunda_presenca = date('Y-m-d H:i:s');
    		}
    		//print_r($presenca);
    		$presenca->save();
    		$msg = 'Presença gravada com sucesso';
    	} catch (Exception $e) {
    		$msg = 'Erro ao gravar a presença';
    	}
    	$this->view->msg = $msg;
    }
}