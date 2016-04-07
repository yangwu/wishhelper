<?php
session_start ();
include dirname ( '__FILE__' ) . './Wish/WishClient.php';
include_once dirname ( '__FILE__' ) . './Wish/WishHelper.php';
include_once dirname ( '__FILE__' ) . './mysql/dbhelper.php';
include_once dirname ( '__FILE__' ) . './user/wconfig.php';
use Wish\WishClient;
use mysql\dbhelper;
use Wish\WishHelper;
use Wish\Model\WishTracker;
use Wish\Exception\ServiceResponseException;
header ( "Content-Type: text/html;charset=utf-8" );
set_time_limit ( 0 );
$dbhelper = new dbhelper ();
$wishHelper = new WishHelper ();

$username = $_SESSION ['username'];
if ($username == null) { // 未登录
	header ( "Location:./wlogin.php?errorMsg=登录失败" );
	exit ();
}

// 已登录
$result = $dbhelper->getUserToken ( $username );
$accounts = array ();
$i = 0;
while ( $rows = mysql_fetch_array ( $result ) ) {
	if($rows ['token'] != null){
		$accounts ['clientid' . $i] = $rows ['clientid'];
		$accounts ['clientsecret' . $i] = $rows ['clientsecret'];
		$accounts ['token' . $i] = $rows ['token'];
		$accounts ['refresh_token' . $i] = $rows ['refresh_token'];
		$accounts ['accountid' . $i] = $rows ['accountid'];
		$accounts ['accountname' . $i] = $rows ['accountname'];
		
		$accounts[$rows ['accountid']] = $rows ['token']; 
		$i ++;
	}
}

$queryParentSKU = $_POST['query_parent_sku'];

$optimizeparams = $dbhelper->getOptimizeParams();
if($oparams = mysql_fetch_array($optimizeparams)){
	$regularInventory = $oparams['inventory'];
	$daysUploaded = $oparams['daysuploaded'];
}

$command = $_POST['command'];
$accountid = $_POST['currentAccountid'];
$client = new WishClient ($accounts[$accountid], 'prod' );
if($command != null && strcmp($command,'updateInventory') == 0){
	
	$SKUS = $dbhelper->getSKUSforInventory($accountid);
	
	$resultSKU = array();
	
	while($skuarray = mysql_fetch_array($SKUS)){
		$sku = $skuarray['sku'];
		$sku = str_replace("&amp;","&",$sku);
		$onlineProductVar = $client->getProductVariationBySKU($sku);
		if($onlineProductVar->inventory< $regularInventory){
			$params = array();
			$params['sku'] = $sku;
			$params['inventory'] = $regularInventory;
			$client->updateProductVarByParams($params);
			$resultSKU[] = $sku;
		}
	}
}else if($command != null && strcmp($command,'salesOptimize') == 0){
	$endDate = date('Y-m-d',strtotime('last monday',time()));
	$startDate = date('Y-m-d',strtotime('last monday',strtotime($endDate)));
	
	$productsResults = $dbhelper->getWeekImpressions($accountid, $startDate, $endDate, $daysUploaded);
}

?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<!-- saved from url=(0031)http://china-merchant.wish.com/ -->
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
	<meta charset="utf-8">
		<meta name="viewport" content="width=device-width, initial-scale=1.0">
			<title>Wish管理助手-更有效率的Wish商户实用工具</title>
			<meta name="keywords" content="">
				<link rel="stylesheet" type="text/css" href="../css/home_page.css">
				<link rel="stylesheet" type="text/css" href="../css/add_products_page.css" />
					<link href="../css/bootstrap.min.css" rel="stylesheet">
						<script src="../js/jquery-2.2.0.min.js"></script>
						<script src="../js/bootstrap.min.js"></script>

</head>
<body>
	<!-- HEADER -->
	<div id="header" class="navbar navbar-fixed-top 
" style="left: 0px;">
		<div class="container-fluid ">
			<a class="brand" href="https://wishconsole.com/"> <span
				class="merchant-header-text">Wish管理助手-更有效率的Wish商户实用工具</span>
			</a>

			<div class="pull-right">
				<ul class="nav">
					<li data-mid="5416857ef8abc87989774c1b"
						data-uid="5413fe984ad3ab745fee8b48">
<?php echo $username?>
</li>
					<li><button>
							<a href="./wlogin.php?type=exit">注销</a>
						</button></li>

				</ul>

			</div>

		</div>
	</div>
	<!-- END HEADER -->
	<!-- SUB HEADER NAV-->
	<!-- splash page subheader-->



	<div id="sub-header-nav" class="navbar navbar-fixed-top sub-header"
		style="left: 0px;">
		<div class="navbar-inner">
			<div class="container-fluid">
				<div class="pull-left">
					<div class="navbar-inner">
						<div class="container">
						
						<ul class="nav">
							<!-- <li><a href="./wusercenter.php"> 订单处理 </a></li> -->
							<li class="dropdown">
								<a href="#" class="dropdown-toggle" data-toggle="dropdown">产品<b class="caret"></b> </a>
								<ul class="dropdown-menu">
								<li><a href="./wuploadproduct.php">产品上传</a></li>
								<li><a href="./wproductstatus.php">定时产品状态</a></li>
								<li><a href="./wproductsource.php">产品源查询</a></li>
								</ul>
							</li>  
							<li class="dropdown">
								<a href="#" class="dropdown-toggle" data-toggle="dropdown">店铺优化<b class="caret"></b> </a>
								<ul class="dropdown-menu">
								<li><a href="./csvupload.php">CSV文档上传</a></li>
								<li><a href="./wproductlist.php">店铺产品同步</a></li>
								<li><a href="./wproductInfo.php">产品优化</a></li>
								</ul>
							</li> 
							<!-- <li><a href="./wuserinfo.php"> 个人信息 </a></li> -->
							<li> <a href="./whelper.php">帮助文档</a></li>
						</ul>
						</div>
					</div>
					<!-- /navbar-inner -->
					<!-- /navbar-inner -->
				</div>

				<div class="pull-right">
					<ul class="nav">
					</ul>
				</div>

			</div>
		</div>
	</div>
	<!-- END SUB HEADER NAV -->
	<div class="banner-container"></div>
	<div id="page-content" class="dashboard-wrapper">
	<form class="form-horizontal" id="optimizeproduct"
			action="./wproductInfo.php" method="post">
			<input type="hidden" id="command" name="command" value=""/>
			<li>已绑定的wish账号:
<?php
for($count = 0; $count < $i; $count ++) {
	if($accounts ['token' . $count] != null)
		echo "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;" . $accounts ['accountname' . $count];
}
?>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<a
				href="./wbindwish.php">绑定wish账号</a>
			</li>
			<br/>
			
			<?php 
	                      		if(isset($resultSKU)){
	                      			echo "<div class=\"alert alert-block alert-success fade in\">";
	                      			echo "<h4 class=\"alert-heading\">对以下的产品库存进行了更新:";
	                      			$count = 0;
	                      			foreach ($resultSKU as $currsku){
	                      				echo "&nbsp;&nbsp;&nbsp;&nbsp;".$currsku.",";
	                      				$count++;
	                      				if($count%10 == 0)
	                      					echo "<br/>";
	                      			}
	                      			echo "</h4>";
	                      			echo "</div>";
	                      			$resultSKU = null;
	                      		}
	                    ?>
	                    
			<div id="add-product-form">
						<div id="basic-info" class="form-horizontal">
							<div class="control-group">
								<label class="control-label" data-col-index="3"><span
									class="col-name">请选择wish账号</span></label>

								<div class="controls input-append">
									<label>
							<?php
							if ($i>0){
								for($count = 0; $count < $i; $count ++) {
									if($count != 0 && $count%3 == 0)
										echo "<br/>";
									echo "<input type=\"radio\" id=\"currentAccountid\" name=\"currentAccountid\" value=\"" . $accounts ['accountid' . $count] . "\"" . ($accountid == null ? ($count == 0 ? "checked" : "") : ((strcmp ( $accounts ['accountid' . $count], $accountid ) == 0) ? "checked" : "")) . ">";
									echo "&nbsp;&nbsp;" . $accounts ['accountname'.$count];
									echo "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;";
								}	
							}else{
								echo "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;您暂时没有绑定任何wish账号，请先&nbsp;&nbsp;&nbsp;&nbsp;";
							}
							
							?></label>
								</div>
							</div>
							
							<div class="control-group">
								<div>
								<ul align="center">
				<button class="btn btn-info" type="button" onclick="updateInventory()">扫描库存</button>
				&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
				<button class="btn btn-info" type="button"
					onclick="downloadlabels()">价格调整</button>
				&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
				<button class="btn btn-info" type="button"
					onclick="uploadtrackings()">运费调整</button>
				&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
				<button class="btn btn-info" type="button"
					onclick="salesOptimize()">每周销量扫描</button>
					</ul>
								</div>
							</div>
						</div>
				</div>
							
							
			<div class="control-group">
				<label class="control-label"><span
									class="col-name">查询parent_sku:</span></label>
						<div class="controls input-append">
							<input class="input-block-level required" id="query_parent_sku"
									name="query_parent_sku" type="text"
									value="<?php echo $parent_sku ?>"
									/>&nbsp;&nbsp;&nbsp;&nbsp;
									<button id="query_action" type="submit"
								class="btn btn-primary btn-large">提交</button>
						</div>
			</div>
			
			
<?php
if($command != null && strcmp($command,'salesOptimize') == 0){
	if(isset($productsResults)){
		echo "<div class=\"row-fluid\"><div class=\"span12\"><div class=\"widget\"><div class=\"widget-header\"><div class=\"title\">&nbsp;&nbsp;&nbsp;&nbsp;账号:&nbsp;&nbsp;" . $accountid."&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;下列产品在上周没有任何推送，并且已经上传产品超过".$daysUploaded."天,建议下架,可优化后重新上架:";
		echo "</div><span class=\"tools\"></div>";
		echo "<div class=\"widget-body\"><table class=\"table table-condensed table-striped table-bordered table-hover no-margin\"><thead><tr>";
		echo "<th style=\"width:25%\">产品名称</th><th style=\"width:20%\">父SKU</th>";
		echo "<th style=\"width:10%\">收藏数</th><th style=\"width:10%\">已售出</th><th style=\"width:10%\">上传时间</th><th style=\"width:10%\">操作</th></tr></thead>";
		echo "<tbody>";
		$orderCount = 0;
		while($productResult = mysql_fetch_array($productsResults)){
			
				if ($orderCount % 2 == 0) {
					echo "<tr>";
				} else {
					echo "<tr class=\"gradeA success\">";
				}
			
				echo "<td style=\"width:25%;vertical-align:middle;\">" . $productResult['name']. "</td>";
				echo "<td style=\"width:20%;vertical-align:middle;\"><ul><li><img width=50 height=50 style=\"vertical-align:middle;\" src=\"" . $productResult ['main_image'] . "\">" . $productResult ['parent_sku'] ."</li><ul></td>";
				echo "<td style=\"width:10%;vertical-align:middle;\">" . $productResult['number_saves']."</td>";
				echo "<td style=\"width:10%;vertical-align:middle;\">" . $productResult ['number_sold']."</td>";
				echo "<td style=\"width:10%;vertical-align:middle;\">" . $productResult ['date_uploaded']."</td>";
				
				if($productResult['number_saves'] == 0 &&  $productResult ['number_sold'] == 0){
					$skus = $wishHelper->getProductVars($productResult['id']);
					foreach ($skus as $sku){
						$params = array();
						$params['sku'] = $sku;
						$params['enabled'] = "false";
						$client->updateProductVarByParams($params);
					}
					echo "<td style=\"width:10%;vertical-align:middle;\"><span class=\"label label-info\">该产品已经自动下架</span></td>";
				}else{
					echo "<td style=\"width:10%;vertical-align:middle;\"><button type=\"button\" onclick=\"productDetails('".$accountid."','".$productResult['id']."')\" class=\"btn btn-mini\"><span class=\"label label-info\">查看</span></button></td>";
				}
				echo "</tr>";
				$orderCount ++;
			
		}
		echo "</tbody></table></div></div></div></div>";
	}
}else{

	$orderCount = 0;
	for($count1 = 0; $count1 < $i; $count1 ++) {
		if($accounts ['token' . $count1] != null){
			$onlineProducts = $dbhelper->getOnlineProducts($accounts ['accountid' . $count1],$queryParentSKU );
			echo "<div class=\"row-fluid\"><div class=\"span12\"><div class=\"widget\"><div class=\"widget-header\"><div class=\"title\">&nbsp;&nbsp;&nbsp;&nbsp;账号:&nbsp;&nbsp;" . $accounts ['accountname' . $count1];
			echo "</div><span class=\"tools\"></div>";
			echo "<div class=\"widget-body\"><table class=\"table table-condensed table-striped table-bordered table-hover no-margin\"><thead><tr>";
			echo "<th style=\"width:25%\">产品名称</th><th style=\"width:20%\">父SKU</th>";
			echo "<th style=\"width:10%\">收藏数</th><th style=\"width:10%\">已售出</th><th style=\"width:10%\">上传时间</th><th style=\"width:10%\">操作</th></tr></thead>";
			echo "<tbody>";
			while ( $cur_product = mysql_fetch_array ( $onlineProducts) ) {
				if ($orderCount % 2 == 0) {
					echo "<tr>";
				} else {
					echo "<tr class=\"gradeA success\">";
				}
				echo "<td style=\"width:25%;vertical-align:middle;\">" . $cur_product['name']. "</td>";
				echo "<td style=\"width:20%;vertical-align:middle;\"><ul><li><img width=50 height=50 style=\"vertical-align:middle;\" src=\"" . $cur_product ['main_image'] . "\">" . $cur_product ['parent_sku'] ."</li><ul></td>";
				echo "<td style=\"width:10%;vertical-align:middle;\">" . $cur_product['number_saves']."</td>";
				echo "<td style=\"width:10%;vertical-align:middle;\">" . $cur_product ['number_sold']."</td>";
				echo "<td style=\"width:10%;vertical-align:middle;\">" . $cur_product ['date_uploaded']."</td>";
				echo "<td style=\"width:10%;vertical-align:middle;\"><button type=\"button\" onclick=\"productDetails('".$accounts ['accountid' . $count1]."','".$cur_product['id']."')\" class=\"btn btn-mini\"><span class=\"label label-info\">查看</span></button></td>";
				echo "</tr>";
				$orderCount ++;
			}
			echo "</tbody></table></div></div></div></div>";
		}
	}
}

?>
</form>
	</div>
	<!-- FOOTER -->
	<div id="footer" class="navbar navbar-fixed-bottom" style="left: 0px;">
		<div class="navbar-inner">
			<div class="footer-container">
				<span><a href="https://wishconsole.com/">关于我们</a></span> <span><a>2016
						wishconsole版权所有 京ICP备16000367号</a>
				</span>
			</div>
		</div>
	</div>
	<!-- END FOOTER -->
	<script type="text/javascript">
		function productDetails(uid,pid){
			window.open("./wproductDetails.php?uid=" + uid + "&pid=" + pid);
		}

		function updateInventory(){
			var form = document.getElementById("optimizeproduct");
			$('#command').val("updateInventory");
			form.submit();
		}

		function salesOptimize(){
			var form = document.getElementById("optimizeproduct");
			$('#command').val("salesOptimize");
			form.submit();
		}
	</script>
	<!-- GoStats JavaScript Based Code -->
<script type="text/javascript" src="https://ssl.gostats.com/js/counter.js"></script>
<script type="text/javascript">_gos='c5.gostats.cn';_goa=1068962;
_got=5;_goi=1;_gol='淘宝店铺计数器';_GoStatsRun();</script>
<noscript><a target="_blank" title="淘宝店铺计数器" 
href="http://gostats.cn"><img alt="淘宝店铺计数器" 
src="https://ssl.gostats.com/bin/count/a_1068962/t_5/i_1/ssl_c5.gostats.cn/counter.png" 
style="border-width:0" /></a></noscript>
<!-- End GoStats JavaScript Based Code -->
</body>
</html>