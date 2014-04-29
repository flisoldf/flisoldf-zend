<?php
/**
 * Controle de participantes
 * @filesource  07/03/2010
 * @author      PHPDF <http://www.phpdf.org.br>
 * @package     <<application>>
 * @subpackage  <<application>>.application.controllers
 * @version     $Id: ParticipantesController.php 163 2010-06-18 14:07:07Z ramon.ornela $
 */
class ParticipantesController extends Phpdf_Controller_Action
{
    /**
     * Painel do participante
     * @return void
     */
    public function indexAction()
    {
        if(!Zend_Auth::getInstance()->hasIdentity()) {
           $this->_redirect('participantes/login');
        } elseif(Zend_Auth::getInstance()->getIdentity()->sRole == Perfil::ADMIN) {
           $this->_redirect('administrador');
        }

        $idUsuario = Zend_Auth::getInstance()->getIdentity()->id;
        $usuario = Usuario::build()->find($idUsuario)->current();

        // Atividades submetidas
        $this->view->atividadesSubmetidas = Atividade::build()->findByPalestrante($idUsuario);

        // Atividades que esta inscrito
        $this->view->atividadesInscrito = Atividade::build()->findByParticipante($idUsuario);

        // Vagas ocupadas/inscrições
        $this->view->vagasOcupadas = Inscricao::build()->countByAtividade();
    }

    public function editarAction()
    {
        $id = $this->_getParam('id', null);
        $participantes = new Usuario();
        if ($id) {
            $rowParticipante = $participantes->find($id)->current();
        } else {
            $rowParticipantes = $participantes->createRow();
        }
        $this->view->participantes = $rowParticipantes;
    }

    /**
     * Cadastro do participante/usuário
     * @return void
     */
    public function cadastrarAction()
    {
        if ($this->getRequest()->isPost()) {
            $usuario      = new Usuario();
            $dados        = $this->_getAllParams();

            // Verifica se ja existe o email cadastrado
            if ($usuario->hasEmail($dados['email'])) {
                $this->_addMessage('Este email já esta cadastrado na nossa base de dados');
                $this->_redirect('participantes/cadastrar');
                return;
            }

            $rowUsuario = $usuario->createRow();
            unset($dados['id']);

            // Usada para o login
            $senhaLimpa           = $dados['senha'];
            $dados['perfil_id']   = 10;
            $dados['dt_cadastro'] = date('Y-m-d h:i:s');
            $dados['senha']       = Usuario::gerarSenha($dados['senha']);
            $rowUsuario->setFromArray($dados);
            try {
                $rowUsuario->save();

                /*$this->_addMessage('Você foi cadastrado com sucesso');*/

                $this->_addMessage('Você foi cadastrado com sucesso, seja bem vindo(a)');

                //envia o e-mail de confirmação de inscrição para o participante
                /*
                $mail    = new Zend_Mail();
                $mail->addTo($dados['email'],$dados['nome']);
                $mail->addTo('arthur.almeidapereira@gmail.com');
                $mail->setSubject('Confirmação Cadastro FLISOL 2010');
                $this->view->nomeParticipanteEmail    = utf8_decode($dados['nome']);
                $mail->setBodyHtml($this->view->render('inscricoes/email_cadastro.phtml'));
                $mail->send();*/

                $usuario = new Usuario();
                if ($usuario->login($dados['email'], $senhaLimpa)) {
                    $this->_redirect('atividades');
                }


            } catch (Exception $e) {
                $this->_addMessage('Seu cadastro não foi realizado');
            }
            $this->_redirect('participantes/cadastrar');
        } else {
            $uf  = new Uf();
            $rowSetUfs = $uf->fetchAll(null, 'nome');

            $ufs = array();
            foreach ($rowSetUfs as $rowUf) {
                $ufs[$rowUf->id] = $rowUf->nome;
            }

            $this->view->ufs = $ufs;
        }
    }

    /**
     * Login dos usuário
     * @return void
     */
    public function loginAction()
    {
        if ($this->_request->isPost()) {
            $usuario = new Usuario();
            if($usuario->login($this->_request->getPost('email'), $this->_request->getPost('senha'))) {
                $this->_redirect('index');
            } else {
                $this->_addMessage('Usuário/email ou senha inválido(s)');
                $this->_redirect('participantes/login');
            }
        }
    }

    /**
     * Logout do usuário
     * @return void
     */
    public function logoutAction()
    {
        $usuario = new Usuario();
        $usuario->logout();
        $this->_redirect('index');
    }

    /**
     * Recupera a senha do usuário
     * @return void
     */
    public function recuperarSenhaAction()
    {
        if ($this->_request->isPost()) {
            $tbUsuario  = new Usuario();
            $rowUsuario = $tbUsuario->fetchRow(array('email = ?' => $this->_getParam('email')));
            if (null === $rowUsuario) {
                $this->_addMessage('E-mail inexistente');
                $this->_redirect('participantes/recuperar-senha');
            }
            $mail = new Zend_Mail('utf-8');
            $mail->addTo($rowUsuario['email'], $rowUsuario['nome'])
                 ->setSubject('[Flisol] Mudar senha')
                 ->setBodyHtml($this->view->partial('email/recuperar-senha.phtml', array('usuario' => $rowUsuario)))
                 ->send();

           $this->_addMessage("Por favor verifique seu e-mail: {$rowUsuario['email']}");
           $this->_redirect('/participantes/recuperar-senha');
        }
    }

    public function mudarSenhaAction()
    {
        $hash  = trim($this->_getParam('hash'));

        if (!$hash) {
            $this->_addMessage('Hash inválido');
            $this->_redirect('/');
        }

        if ($this->_request->isPost()) {
            $senha = $this->_getParam('senha');
            if ($senha !== $this->_getParam('senha-confirmacao')) {
                $this->_addMessage('Senha inválida');
                $this->_redirect("/participantes/mudar-senha/hash/{$hash}");
            }
            list($id, $email) = explode('+', base64_decode($hash));
            $tbUsuario  = new Usuario();
            $rowUsuario = $tbUsuario->fetchRow(
                array(
                    'id    = ?' => $id,
                    'email = ?' => $email
                )
            );

            if (null === $rowUsuario) {
                $this->_addMessage('Usuário inexistente');
                $this->_redirect('/');
            }
            $rowUsuario->senha = Usuario::gerarSenha($senha);
            try {
                $rowUsuario->save();
            } catch (Exception $e) {
                $this->_addMessage('Não foi possível mudar sua senha!');
                $this->_redirect("/participantes/mudar-senha/hash/{$hash}");
            }
            $this->_addMessage('Mudança de senha efetuada com sucesso!');
            $this->_redirect('/');
        }

        $this->view->hash = $hash;
    }

    public function participacaoAtividadesAction()
    {
        $idUsuario  = Zend_Auth::getInstance()->getIdentity()->id;
        $tbUsuario  = new Usuario();
        $usuario    = $tbUsuario->find($idUsuario)->current();
        
        $tbAtividade  = new Atividade();
        $palestrante   = $tbAtividade->fetchRow(array('id_palestrante = ?' => $idUsuario));
        if ($palestrante) {
        $arrPalestrante = $palestrante->toArray();
        } else {
        $arrPalestrante = array();
        }

        $tbPresenca  = new Presenca();
        $presenca   = $tbPresenca->fetchRow(array('usuario_id = ?' => $idUsuario));
        
        if ($presenca) {
        $arrPresenca = $presenca->toArray();
        } else {
        $arrPresenca = array();
        }

        $this->view->colaborador = $usuario->colaborador;
        $this->view->presenca = $arrPresenca;
        $this->view->palestrante = $arrPalestrante;
    }
    
    //Verificar Certificado
    public function codecertificadoAction()
    {
    $idUsuario  = Zend_Auth::getInstance()->getIdentity()->id;
        $tbUsuario  = new Usuario();
        $usuario    = $tbUsuario->find($idUsuario)->current();
       	$this->view->usuario = $usuario;
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
    }

    /**
     * Imprimir certificado
     * @return void
     */
    public function certificadoAction()
    {
        $this->_helper->layout()->disableLayout();
        $tipo = $this->_getParam('tipo');
        
        $idUsuario  = Zend_Auth::getInstance()->getIdentity()->id;
        $tbUsuario  = new Usuario();
        $usuario    = $tbUsuario->find($idUsuario)->current();
       	$this->view->usuario = $usuario;
        
        $tbAtividade  = new Atividade();
        $palestrante   = $tbAtividade->fetchAll(array('id_palestrante = ?' => $idUsuario));
        if ($palestrante) {
        $arrPalestrante = $palestrante->toArray();
        } else {
        $arrPalestrante = array();
        }
               
        $this->view->participante = Zend_Auth::getInstance()->getIdentity();
        $this->view->palestrante = $arrPalestrante;
        
        $textoParticipante = 'Certificamos que <strong>'.$usuario['nome'].'</strong> participou do<br />
    <strong>Festival Latino Americano de Instalação de Software Livre <br>
    ( FLISOL-DF 2014 )</strong><br> 
    realizado no <strong>Distrito Federal</strong><br />
    com carga horária de 8 horas.';
    	$textoPalestrante = 'Certificamos que <strong>'.$usuario['nome'].'</strong> foi palestrante do<br />
    <strong>Festival Latino Americano de Instalação de Software Livre <br>
    ( FLISOL-DF 2014 )</strong><br>
    realizado no <strong>Distrito Federal</strong><br />
    e apresentou a(s) seguinte(s) atividade(s):';
    	$textoColaborador = 'Certificamos que <strong>'.$usuario['nome'].'</strong> <br>
    	trabalhou como colaborador na organização do<br />
    <strong>Festival Latino Americano de Instalação de Software Livre <br>
    ( FLISOL-DF 2014 )</strong><br>
    realizado no <strong>Distrito Federal</strong>.<br><br>
    Carga horária de 30 horas.';
    
    	$this->view->textoParticipante = $textoParticipante;
    	$this->view->textoPalestrante = $textoPalestrante;
    	$this->view->textoColaborador = $textoColaborador;
    	
    	$this->view->tipo = $tipo;
    }

    public function certificadoColaboradorAction()
    {
        if (Zend_Auth::getInstance()->getIdentity()->colaborador != Usuario::COLABORADOR) {
            $this->_addMessage('Você não esta definido como colaborador, por favor, procure o administrador');
            //$this->_redirect('participantes/participacao-atividades');
        }
        $this->_helper->layout()->disableLayout();
        if ($this->_getParam('tipo') === 'pdf') {
            $pdf   = new Phpdf_Pdf();
            $pdf->emitirCertificadoColaborador(Zend_Auth::getInstance()->getIdentity()->nome);
        } else {
            $this->view->participante = Zend_Auth::getInstance()->getIdentity();
        }
    }
    public function fichaParticipanteAction()
	{
		$dados   = $this->_getAllParams();
    	//$id = $this->_getParam('id', null);
    	$id = Zend_Auth::getInstance()->getIdentity()->id;
        $usuario = Usuario::build()->fetchRow("id = $id")->toArray();
    	$this->view->usuario = $usuario;
    	$this->view->atividadesInscrito = Atividade::build()->findByParticipante($id);
	}
	
    public function editarParticipanteAction()
    {
    
    	if ($this->getRequest()->isPost()) {

    		$dados = $this->_getAllParams();
    		//$id = $this->_getParam('id', null);
    		$id = Zend_Auth::getInstance()->getIdentity()->id;
        	$usuario = new Usuario();
    		$rowUsuario = $usuario->find($id)->current();
    		$rowUsuario->setFromArray($dados);
    		
    		$rowUsuario->save();
    		
    		$this->_redirect('administrador/listagem/tipo/' . Perfil::PARTICIPANTE);

    	} else { 
		
    	$id = Zend_Auth::getInstance()->getIdentity()->id;
                
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
}