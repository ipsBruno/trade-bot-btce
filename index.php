
<?php

/*

 # Bitcoin Trade. BTC-E Bot
 # Por Bruno da Silva - ipsBruno
 # down2money.net mixmusicas.com.br
 # email@brunodasilvacom
 
*/

error_reporting(E_ALL);
set_time_limit(0);


#Carregar biblioteca
include 'btce.php';

# Codificaçío da página
header('Content-Type: text/html; charset=utf-8');


# Configurações de API, coloque suas cháves aqui
$api = '';
$key = '';


# Fazer autenticaçíoo no BTC-E
$BTCeAPI = new BTCeAPI($api, $key);

# Pegar informações básicas
$ordens = 0;
$bitcoins = 0;
$dolares = 0;
$servidor = array();


# Valor de USD para fazer Trade! 
# Lembrando que este valor, precisa estar disponí­vel no BTC-e
$tradeusd = 8;

# Esse é o valor de imposto cobrado a cada transaçío na exchange, em porcentagem
$fee = 0.4;


# Tempo para comprar máximo ordem (em minutos)
$tempo = 20;


# Checar se há ordem pra venda aberta
if(isset($_GET["c"]))
{
	if(@ChecarOrdem($_GET["c"])) 
	{
		$oid = $_GET["c"];	
		
		die("Aguardando fechamento da ordem de venda .. <script>setTimeout(function(){location.href='index.php?c=".$oid."';},5000);</script>");
	}
	die("Ordem fechada com sucesso, criando ordem pra compra .. <script>setTimeout(function(){location.href='index.php';},5000);</script>");
}



# Pegar informações básicas do usuários (ordens abertas, bitcoins, doláres)
try {

    $informacoes = $BTCeAPI->apiQuery('getInfo');

    $ordens = $informacoes["return"]["open_orders"];

    $bitcoins = $informacoes["return"]["funds"]["btc"];

    $dolares = $informacoes["return"]["funds"]["usd"];


} catch(BTCeAPIException $e) {
    RefazerTrade($e->getMessage());
}


# Verificar se a quantia para trade está disponí­vel

if($dolares < $tradeusd) {
   RefazerTrade("Você não tem valor de bitcoins ou doláres  suficiente para trade!");
}


# Pegar qual modo fazer trade, por doláres, ou por bitcoins
$servidor['ticker'] = $BTCeAPI->getPairTicker('btc_usd');

$max = $servidor["ticker"]["ticker"]["buy"] ;
$min  = $servidor["ticker"]["ticker"]["sell"];


if($max < $min)  RefazerTrade("Valor nao encontrado");


recalcular:
$lucro = $max - $min;
$prejuizo =    ($max * $fee / 100) + ($min * $fee / 100) ;                                                                       ;
if($lucro < $prejuizo)  {
	$max += 0.50;
	$min -= 0.50;
	goto recalcular;
}

echo "[ALGORÍTIMO PARA PROCURA DE LUCRO]<br/> Estimativa de lucro <b>".$lucro."$</b><br/>Estimativa de prejuizo:<b>".$prejuizo."$</b><br/>Valor de compra: <b>

{$min}$</b><br/>Valor de venda:<b>{$max}$</b><br/><br/>";


# Calcular valor do BTC a ser comprado
$comprarbtc =  round(GetBitcoinAmmount($tradeusd, $min), 4);

# Abrir ordem para compra de BTCs!
$oid = @ComprarBitcoins( $min, $comprarbtc );

# Verificar se um ID foi gerado
if($oid == 0) RefazerTrade("<br/>[ERRO] Ocorreu um erro ao criar as ordens!") ;

# Arrumar o flush!
force_flush();


# Enquanto tiver ordens

$tentativas = 0;

while(@ChecarOrdem($oid)) {
	$tentativas ++;
	
	Sleep(5);
	
	if($tentativas > $tempo*60/5) {
	
		btce_query("CancelOrder", array("order_id" => $oid), $api, $key);
		RefazerTrade("Não consegui fazer trade desta vez! ORDEM CANCELADA. Tentando novamente");
	
	}
}


# Emitir comunicado de nota fechada!
echo "<br/>[COMPRA] Ordem Fechada.<br/>";


	
# Criar nova ordem para venda
$oid = @VenderBitcoins($max, GetBitcoinFreeFee($comprarbtc, $fee)) ;

# Verificar se um ID foi gerado
if($oid == 0) RefazerTrade("<br/>[ERRO] Ocorreu um erro ao criar as ordens!") ;



# Finalizar, checando periodicamente se a segunda ordem foi fechada tambem
die("<script>setTimeout(function(){location.href='index.php?c=".$oid."';},5000);</script>");


 
##########################
#
# Funções para o sistema
#
##########################

function GetBitcoinFreeFee($bitcoin, $fee) {
	return $bitcoin -= ($bitcoin * $fee / 100);
}

function GetBitcoinValue($dolares, $bitcoin) {
	return $dolares / $bitcoin;
}

function GetBitcoinAmmount($dolares, $value) {
	return $dolares / $value;  
}


function force_flush() {
    echo "\n\n<!-- Deal with browser-related buffering by sending some incompressible strings -->\n\n";
    for ( $i = 0; $i < 5; $i++ )
        echo "<!-- 

abcdefghijklmnopqrstuvwxyz1234567890aabbccddeeffgghhiijjkkllmmnnooppqqrrssttuuvvwwxxyyzz11223344556677889900abacbcbdcdcededfefegfgfhghgihihjijikjkjlklkmlmlnmnmononpopo

qpqprqrqsrsrtstsubcbcdcdedefefgfabcadefbghicjkldmnoepqrfstugvwxhyz1i234j567k890laabmbccnddeoeffpgghqhiirjjksklltmmnunoovppqwqrrxsstytuuzvvw0wxx1yyz2z113223434455666777

889890091abc2def3ghi4jkl5mno6pqr7stu8vwx9yz11aab2bcc3dd4ee5ff6gg7hh8ii9j0jk1kl2lmm3nnoo4p5pq6qrr7ss8tt9uuvv0wwx1x2yyzz13aba4cbcb5dcdc6dedfef8egf9gfh0ghg1ihi2hji3jik4jk

j5lkl6kml7mln8mnm9ono -->\n\n";
    while ( ob_get_level() )
        ob_end_flush();
    @ob_flush();
    @flush();
} # force_flush()



function VenderBitcoins($valorbtc, $quantosbtc) {
	global $BTCeAPI;
	
	try {
		echo "[VENDA] Ordem Criada: $quantosbtc btc. Cotaçío: $valorbtc<br/>";
		$r = $BTCeAPI->makeOrder( round($quantosbtc,3) , 'btc_usd', BTCeAPI::DIRECTION_SELL, round($valorbtc,3));
					
		return $r["return"]["order_id"];		
		
	} catch(BTCeAPIInvalidParameterException $e) {
		echo $e->getMessage();
		return false;
		
	} catch(BTCeAPIException $e) {
		echo $e->getMessage();
		return false;
	}
	

}


function ComprarBitcoins($valorbtc, $quantosbtc) {
	global $BTCeAPI;
	
	
	try {
		echo "[COMPRA] Ordem Criada: $quantosbtc btc. Cotação: $valorbtc<br/>";	
		$r = $BTCeAPI->makeOrder( round($quantosbtc,3) , 'btc_usd', BTCeAPI::DIRECTION_BUY, round($valorbtc,3));
			
		return $r["return"]["order_id"];
		
	} catch(BTCeAPIInvalidParameterException $e) {
		echo $e->getMessage();
		return false;
		
	} catch(BTCeAPIException $e) {
		echo $e->getMessage();
		return false;
	}
	
}


function ChecarOrdem($id) {
	global $BTCeAPI;
	
	try {
    
    		$params = array('pair' => 'btc_usd');
    
    		$r = ($BTCeAPI->apiQuery('ActiveOrders', $params));
    
    		if(count($r["return"][$id])) return true;
	} 
	catch(BTCeAPIException $e) {
    		return false;
	}	
	return false;		
}



function btce_query($method, array $req = array(), $key, $secret) {
        
  
 
        $req['method'] = $method;
        $mt = explode(' ', microtime());
        $req['nonce'] = $mt[1];
       
        // generate the POST data string
        $post_data = http_build_query($req, '', '&');
 
        $sign = hash_hmac('sha512', $post_data, $secret);
 
        // generate the extra headers
        $headers = array(
                        'Sign: '.$sign,
                        'Key: '.$key,
        );
 
        // our curl handle (initialize if required)
        static $ch = null;
        if (is_null($ch)) {
                $ch = curl_init();
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/4.0 (compatible; BTCE PHP client; '.php_uname('s').'; PHP/'.phpversion().')');
        }
        curl_setopt($ch, CURLOPT_URL, 'https://btc-e.com/tapi/');
        curl_setopt($ch, CURLOPT_POSTFIELDS, $post_data);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
 
        // run the query
        $res = curl_exec($ch);
        if ($res === false) throw new Exception('Could not get reply: '.curl_error($ch));
        $dec = json_decode($res, true);
        if (!$dec) throw new Exception('Invalid data received, please make sure connection is working and requested API exists');
        return $dec;
}


function RefazerTrade($motivo) {
   print "<script>setTimeout(function(){location.reload();},5000);</script>";
   die($motivo);
}
 
 
?>
