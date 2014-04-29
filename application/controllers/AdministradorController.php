<?php
/**
 * @filesource  07/03/2010
 * @author      PHPDF <http://www.phpdf.org.br>
 * @package     <<application>>
 * @subpackage  <<application>>.application.controllers
 * @version     $Id: AdministradorController.php 152 2010-06-10 21:14:32Z cristianoteles $
 */
class AdministradorController extends Phpdf_Controller_Action
{
	public function codeAction(){
		$code = $this->_getParam('code', null);
		
		$usuario = Usuario::build()->fetchRow("id = $code")->toArray();
		$presenca = Presenca::build()->fetchRow("usuario_id = $code")->toArray();
		if ($usuario) {
			if (!$presenca) {
				Presenca::build()->getDefaultAdapter()->beginTransaction();
				Presenca::build()->update(array('data_primeira_presenca' =>time() ),'usuario_id='. $code);
				Presenca::build()->getDefaultAdapter()->commit();
				$this->view->msg = 'Primeira Presença marcada com sucesso';
			} else {			
				Presenca::build()->getDefaultAdapter()->beginTransaction();
				Presenca::build()->update(array('data_segunda_presenca' =>time() ),'usuario_id='. $code);
				Presenca::build()->getDefaultAdapter()->commit();
				$this->view->msg = 'Segunda Presença marcada com sucesso';
			}
			$this->view->error = false;
			
		} else {
			$this->view->error = 'Usuário não encontrado!';
		}
	}
    /**
     * Painel do administrador
     * @return void
     */
    public function indexAction()
    {
        $idUsuario = Zend_Auth::getInstance()->getIdentity()->id;

        // Atividades submetidas
        $this->view->atividadesSubmetidas = Atividade::build()->fetchAll('situacao = ' . Atividade::SUBMETIDO, 'nome');

        // Atividades
        $this->view->atividades = Atividade::build()->findAll();

        // Atividades rejeitadas
        $this->view->atividadesRejeitadas = Atividade::build()->fetchAll('situacao = ' . Atividade::REJEITADO, 'nome');

        // Usuários cadastrados
        $this->view->qtUsuarios   = Usuario::build()->fetchAll()->count();

        // Usuários cadastrados
        $this->view->qtInscricoes = Inscricao::build()->fetchAll()->count();

        // Vagas ocupadas/inscrições
        $this->view->vagasOcupadas = Inscricao::build()->countByAtividade();

        //Atividades que o admin está inscritos
        $this->view->atividadesInscrito = Atividade::build()->findByParticipante($idUsuario);
    }

    public function listagemAction()
    {
    	$letra_q = $this->_getParam('letra', 'A');
		$this->view->letra_list = $letra_q;
		
		
		if($this->_getParam('tipo') == Perfil::PARTICIPANTE) {
            
            $usuarios = Usuario::build()->fetchAll('nome like "'.$letra_q.'%"', 'nome');
            $presencas = Presenca::build()->fetchAll();
            
            $this->view->usuarios = $usuarios;
            $this->view->presencas = $presencas->toArray();
            
            $this->render('listagem-usuario');
        } else if($this->_getParam('tipo') == Perfil::PALESTRANTE) {
            $this->view->usuarios = Usuario::build()->findPalestrante();
        } else if($this->_getParam('tipo') == 'sorteio') {
        	$n_sorteado = $this->_getParam('num_sorteado');
        	if ( $n_sorteado ) {
        		Presenca::build()->update(array('sorteado' =>1, 'premio' => $this->_getParam('premio')),'usuario_id='. $n_sorteado);
        		$usuarios = $usuario = Usuario::build()->fetchRow("id = $n_sorteado")->toArray();
        		$this->view->n_sorteado = $n_sorteado;
        	} else {
        		$usuarios = Usuario::build()->sortParticipante();
            }
            $this->view->usuarios = $usuarios;
            $this->render('listagem-sorteio');
        } else {
        	$usuarios = Usuario::build()->sorteadosParticipante();
        	$this->view->usuarios = $usuarios;
	        $this->render('listagem-sorteados');
        }
    }
    
    public function editarParticipanteAction()
    {
    
    	if ($this->getRequest()->isPost()) {

    		$dados   = $this->_getAllParams();
    		$id = $this->_getParam('id', null);
        	$usuario = new Usuario();
    		$rowUsuario = $usuario->find($id)->current();
    		$rowUsuario->setFromArray($dados);
    		
    		//print_r($rowUsuario);
    		
    		$rowUsuario->save();
    		
    		$this->_redirect('administrador/listagem/tipo/' . Perfil::PARTICIPANTE);

    	} else { 
    	$id = $this->_request->getParam('id',false);
                
        $usuario = Usuario::build()->fetchRow("id = $id")->toArray();

    	$this->view->usuario = $usuario;

    	// Usuários cadastrados
        $this->view->qtUsuarios   = Usuario::build()->fetchAll()->count();

        // Usuários cadastrados
        $this->view->qtInscricoes = Inscricao::build()->fetchAll()->count();
        
        $uf  = new Uf();
        $rowSetUfs = $uf->fetchAll(null, 'nome');

            $ufs = array();
            foreach ($rowSetUfs as $rowUf) {
                $ufs[$rowUf->id] = $rowUf->nome;
            }

        $this->view->ufs = $ufs;
        
        $perfil  = new Perfil();
        $rowSetPerfis = $perfil->fetchAll(null, 'nome');

            $perfis = array();
            foreach ($rowSetPerfis as $rowPerfil) {
                $perfis[$rowPerfil->id] = $rowPerfil->nome;
            }

        $this->view->perfis = $perfis;
               
       }
    }

    public function marcarPresencaAction()
    {
        $this->_isAjax();
        $arrParticipante    = $this->_request->getParam('usuario',false);
        $idAtividade        = $this->_request->getParam('id_atividade',false);
        Inscricao::build()->getDefaultAdapter()->beginTransaction();
        try {
            Inscricao::build()->update(array('presenca' =>'N'),'id_atividade='. $idAtividade);
            foreach($arrParticipante as $idUsuario) {
                Inscricao::build()->marcarPresenca($idAtividade,$idUsuario);
            }
            Inscricao::build()->getDefaultAdapter()->commit();
            echo 'Presença marcada com sucesso';
        } catch (Exception $e) {
            Inscricao::build()->getDefaultAdapter()->rollBack();
            echo $e->getMessage();
        }
    }

    public function emailAction()
    {
        if ($this->getRequest()->isPost()) {
            Zend_Debug::dump($this->_request->getParams());
        }
    }

    public function gravarColaboradorAction()
    {
		$todos_lista = (array) $this->_getParam('todos_lista');
        $colaboradores = (array) $this->_getParam('colaborador');
		$letra = $this->_getParam('letra');
        $administradores = (array) $this->_getParam('admin');
        $presencas1 = (array) $this->_getParam('presenca1');
        $presencas2 = (array) $this->_getParam('presenca2');
        

        // Configura Colaboradores
        $tbUsuario = new Usuario();
        $where_zero = 'id IN ('. implode(', ', $todos_lista) . ')';
        $tbUsuario->update(array('colaborador' => 'null'), $where_zero);

        if (count($colaboradores) > 0) {
            $where = 'id IN ('. implode(', ', $colaboradores) . ')';
            $tbUsuario->update(array('colaborador' => Usuario::COLABORADOR), $where);
        }
        
        // Configura Administradores        
        $tbUsuario = new Usuario();
        $where_zero = 'id IN ('. implode(', ', $todos_lista) . ')';
        $tbUsuario->update(array('perfil_id' => 10), $where_zero);
        if (count($administradores) > 0) {
            $tbUsuario = new Usuario();
            $where = 'id IN ('. implode(', ', $administradores) . ')';
            $tbUsuario->update(array('perfil_id' => 1), $where);
		}

		
		// Configura Presenças
		$tbPresenca = new Presenca();
		$where_zero = 'id IN ('. implode(', ', $todos_lista) . ')';
        $where_zero = $tbPresenca->delete($where_zero);
        
        if (count($presencas1) > 0) {
        	foreach($presencas1 as $user) { 
	            $tbPresenca = new Presenca();
	            $row = $tbPresenca->createRow();
	            $row->usuario_id = $user;
	            $row->data_primeira_presenca = '2013-04-27 09:00:00';
	            $row->evento_id = 1;
	            $row->save();
	        }
		}
        if (count($presencas2) > 0) {
            $where = 'usuario_id IN ('. implode(', ', $presencas2) . ')';
            $tbUsuario = new Presenca();
            $tbUsuario->update(array('data_segunda_presenca' => '2013-04-27 17:00:00'), $where);
		}
		
		
        $this->_addMessage('Usuários gravados com sucesso');
        $this->_redirect('administrador/listagem/tipo/' . Perfil::PARTICIPANTE);
    }
}
