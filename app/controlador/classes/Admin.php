<?php
  /*
  * @author João Artur
  * @description www.joaoartur.com - www.github.com/JoaoArtur
  */

  class Admin {
    public static $controle = '';

    public static function logado() {
      if (isset($_SESSION['dados'])) {
        return true;
      } else {
        return false;
      }
    }

    public static function mostrar($campo) {
      if (isset($_SESSION['dados'])) {
        $sql = "SELECT * FROM administrador_usuarios WHERE id=0".$_SESSION['dados'];
        $qr  = DB::executar($sql);
        if ($qr->rowCount() > 0) {
          $r = $qr->fetch(PDO::FETCH_OBJ);
          return $r->$campo;
        }
      } else {
        return false;
      }
    }

    public static function secao($pagina) {
      $sql = "SELECT * FROM administrador_secao WHERE pagina='$pagina' AND nivel >= 0".self::mostrar('nivel');
      $qr  = DB::executar($sql);
      if ($qr->rowCount() > 0) {
        $r = $qr->fetch(PDO::FETCH_OBJ);
        if (file_exists('app/controlador/admin/'.$r->controle.'.php')) {
          include 'app/controlador/admin/'.$r->controle.'.php';
          self::$controle = $r->controle;
          new $r->controle;
        } else {
          Carregar::view('erro.404');
        }
      } else {
        Carregar::view('erro.404');
      }
    }

    public static function montarMenu() {
      if (self::logado()) {
        $dados = $_SESSION['dados'];
        $sql   = "SELECT * FROM administrador_secao WHERE nivel >= 0".self::mostrar('nivel')." ORDER BY ordem ASC";
        $qr    = DB::executar($sql);
        if ($qr->rowCount() > 0) {
          $menu = [];
          while ($row = $qr->fetch(PDO::FETCH_ASSOC)) {
            echo '
              <li>
                <a href="'.Config::mostrar('PASTA_PADRAO').Config::mostrar('PASTA_ADMIN').$row['pagina'].'">
                  <i class="fa '.$row['icone'].'"></i>
                  '.$row['nome'].'
                </a>
              </li>
            ';
          }
        }
      }
    }

    public static function logar() {
      $usuario = Entrada::post('usuario');
      $senha   = Criptografar::md5(Entrada::post('senha'));

      $sql = "SELECT * FROM administrador_usuarios WHERE usuario='$usuario' and senha='$senha' and ativo=1";
      $qr  = DB::executar($sql);
      if ($qr->rowCount() > 0) {
        $r = $qr->fetch(PDO::FETCH_ASSOC);
        $_SESSION['dados'] = $r['id'];
        header('Location:'.Config::mostrar('PASTA_PADRAO').Config::mostrar('PASTA_ADMIN'));
        return ['status'=>true,'msg'=>'Logado com sucesso!'];
      } else {
        return ['status'=>false,'msg'=>'Usuário e/ou senha incorretos'];
      }
    }
  }
?>