<?
/** 
  * Classe generica para trabalhar com tabelas
  * Elton Luis Minetto <eminetto at gmail dot com>
  * Licenca: GPL 
  * @package framework	
  */

include("adodb/adodb.inc.php"); //a classe depende do adodb
include("adodb/adodb-exceptions.inc.php");

class tabela {
	/**
	* nome da tabela
	* @var string
	*/
	protected $tabela; 

	/**
	* conexao com a base de dados
	* @var string
	*/
	protected $db;
	

	/**
	* array com os dados usados para resultado
	* @var string[]
	*/
	public $dados_result;

	/**
	* array com os dados usados para insert e update
	* @var string[]
	*/
	public $dados_dml;

	/**
	* array usado pelo adodb para pegar os resultados das consultas
	* @var string
	*/
	public $result;

	/**
	* Construtor da classe
	* @param string $tabela O nome da tabela
	* @return void
	*/
	public function __construct($tabela) {
		$this->tabela = $tabela;
		try {
			$this->db = NewADOConnection(app::$db_string);
		} catch (Exception $e) {
			echo "Erro na conexao:".$e->getMessage();
		}
		$this->dados_result = array();
		$this->dados_dml = array();
	}
	/**
	* Funcao que altera o valor da propriedade tabela
	* @param string $tabela Nome da tabela
	* @return void
	*/
	public function setTabela($tabela) {
		$this->tabela = $tabela;
	}

	/**
	* Funcao que monta a consulta sql para a busca dos dados
	* @param string[] $campos Array com o nome dos campos a serem buscados
	* @param string $where Parametros SQL para a pesquisa
	* @return void
	*/
	public function get($campos,$where=null) {
		//monta o sql 
		$sql = "select ";
		$sql .= implode(",",$campos);
		$sql .= " from ".$this->tabela;
		if($where) {
			$sql .= " where ".$where;
		}
		try { 
			$this->result = $this->db->Execute($sql);
		} catch (Exception $e) {
			echo "Erro na pesquisa:<br>Erro:".$e->getMessage().'<br>SQL:'.$sql.'<br><a href="javascript:history.go(-1)">Voltar</a>';
		}
	}

	/**
	* Funcao que retorna um valor booleano indicando se ainda existem resultados
	* @return bool
	*/	
	public function result() {
		try {
			if($this->dados_result = @array_change_key_case($this->result->FetchRow(), CASE_LOWER)){ //recebe o array resultante e converte as chaves para minusculo
				return true;	
			}
			else {
				return false;
			}
		} catch (Exception $e)	{
			echo $e->getMessage();
		}
	}


	/**
	* Funcao que faz o insert dos dados na tabela
	* @return void
	*/
	public function insert() {
		$this->db->BeginTrans( );		
		$sql = "insert into ".$this->tabela."(";
		$sql .= implode(",",array_keys($this->dados_dml));
		$sql .= ") values (";
		$sql .= implode(",",$this->dados_dml);
		$sql .= ')';
		try { 
			$this->result = $this->db->Execute($sql);
			$this->dados_dml = array();
		} catch (Exception $e) {
			echo 'Erro na insercao:'.$e->getMessage().'<br><a href="javascript:history.go(-1)">Voltar</a>';
			exit;
		}
		
	}
	/**
	* Funcao que faz o update dos dados na tabela
	* @param string $where Parametros SQL para a alteracao
	* @return void
	*/
	public function update($where) {
		$this->db->BeginTrans( );
		$sql = "update ".$this->tabela." set ";
		foreach($this->dados_dml  as $campo => $valor) {
			$sql .= "$campo = $valor,";
		}
		$sql = substr($sql,0,strlen($sql)-1);//remove a ultima virgula
		$where = stripslashes($where);
		$sql .= " where $where";
		try { 
			$this->result = $this->db->Execute($sql);
			unset($this->dados_dml);
		} catch (Exception $e) {
			echo "Erro na atualiza��o:<br>SQL:".$sql.'<br>Erro:'.$e->getMessage().'<br><a href="javascript:history.go(-1)">Voltar</a>';
			exit;
		}
	}

	/**
	* Funcao que faz a exclusao dos dados na tabela
	* @param string $where Parametros SQL para a exclusao
	* @return void
	*/
	public function delete($where=null) {
		$this->db->BeginTrans( );
		$sql = "delete from ".$this->tabela;
		if($where)
			$sql .= " where ".stripslashes($where);
		//echo $sql;
		try { 
			$this->result = $this->db->Execute($sql);
		} catch (Exception $e) {
			echo "Erro na exclusão:".$e->getMessage().'<br><a href="javascript:history.go(-1)">Voltar</a>';
			exit;
		}
	}

	/**
	 * Interceptador __set. Quando um valor eh alterado ele eh colocano no array de dados
	 * para ser usado em instrucoes DML (insert, update) 
	 */
	function __set($name,$value) {
		$this->dados_dml[$name] = "'".$value."'";
	}

	/**
	 * Inserceptador __get. Quando um valor eh solicitado eh entregue o valor
	 * do array de resultados das consultas
	 */
	function __get($name) {
		$name = strtolower($name);
		if($name != "dados_result")
			return $this->dados_result[$name];
		else
			return $this->dados_result;
	}

	/**
	* Funcao que faz a confirmacao das operacoes
	* @return void
	*/
	public function save() {
		$this->db->CommitTrans( );
	}

	/**
	* Destrutor da classe
	* @return void
	*/
	public function __destruct() {
		$this->db->close();
	}
}?>
