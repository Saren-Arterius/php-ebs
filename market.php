<?php
//二手市场插件
//Provided and Written By: Kermit
//Debug & Amendments By: IE玩 Website: http://www.iewan.com/
//php-eb v0.25Final SP2 Alterations Officially Made By: v2Alliance
$mode = ( isset($_GET['action']) ) ? $_GET['action'] : $_POST['action'];
include('cfu.php');
postHead('');
AuthUser("$Pl_Value[USERNAME]","$Pl_Value[PASSWORD]");
if ($CFU_Time >= $TIMEAUTH+$TIME_OUT_TIME || $TIMEAUTH <= $CFU_Time-$TIME_OUT_TIME){echo "连线超时！<br>请重新登入！";exit;}
GetUsrDetails("$Pl_Value[USERNAME]",'Gen','Game');
//GUI
if ($actionb=='none'){
        echo "<b style=\"font-size:12px;\">二手市场<hr>";
        echo "<br>";
        echo "<form action=market.php?action=main method=post name=mainform>";
        echo "<input type=hidden value='none' name=actionb>";
        echo "<input type=hidden value='$Pl_Value[USERNAME]' name=Pl_Value[USERNAME]>";
        echo "<input type=hidden value='$Pl_Value[PASSWORD]' name=Pl_Value[PASSWORD]>";
        echo "<input type=hidden name=\"TIMEAUTH\" value=\"$CFU_Time\">";
        echo "<script language=\"Javascript\">";
        echo "function cfmsell(){";
        echo "if($Gen[cash] < mainform.price.value){alert('你的现金不足呢！');mainform.remit.style.visibility='visible';return false;}";
        echo "if (confirm('确定要花'+mainform.price.value+'元购买吗？') == true){mainform.submit();return true}else {mainform.remit.style.visibility='visible';return false;}";

        echo "}</script>";

        $wep_list = ("SELECT * FROM `".$GLOBALS['DBPrefix']."phpeb_user_market` WHERE 1 ORDER BY `id`");
        $query = mysql_query($wep_list);
        while($temp = mysql_fetch_array($query)) {
        $OwnerName_SQL = ("SELECT `gamename` FROM `".$GLOBALS['DBPrefix']."phpeb_user_game_info` WHERE `username` = '$temp[owner]' LIMIT 1;");
        $O_Query = mysql_query($OwnerName_SQL);
        $OName = mysql_fetch_array($O_Query);
        $wep_specs=ReturnSpecs($temp['spec']);
        $weplist .= "<tr class=b>
        <td><div align='center'>$OName[gamename]</div></td>
        <td><div align='center'>$temp[name]</div></td>
        <td><div align='center'>$temp[enc]</div></td>
        <td><div align='center'>$temp[atk]</div></td>
        <td><div align='center'>$temp[hit]</div></td>
        <td><div align='center'>$temp[rd]</div></td>
        <td><div align='center'>$wep_specs</div></td>
        <td><div align='center'>$temp[price]</div></td>
        <td><div align='center'><input type=radio name=actionb value=remit onClick=\"price.value=$temp[price];mainform.sellid.value='$temp[id]';mainform.wepid.value='$temp[wepid]';mainform.owner.value='$temp[owner]';remit.disabled=false;\"></div></td>
        </tr>";
        }
        echo "<input type=hidden name=sellid value=0 maxlength=10 size=10>";
        echo "<input type=hidden name=wepid value=0 maxlength=10 size=10>";
        echo "<input type=hidden name=price value=0 maxlength=10 size=10>";
        echo "<input type=hidden name=owner value=0 maxlength=10 size=10>";
        echo "<p align=center style=\"font-size: 16; font-family: Arial\">委託出售商品一览:</p>";
        echo "<table width=\"100%\" align=center border=\"0\" cellpadding=\"0\" cellspacing=\"0\" style=\"border-collapse: collapse;font-size: 12; font-family: Arial\" bordercolor=\"#FFFFFF\">";
        echo "<tr><td>";
        echo "<table width=\"100%\" border=\"1\" align=center cellspacing=\"0\" cellpadding=\"0\">";
        echo '<td width="10%"><div align="center">托售者</div></td>';
        echo '<td width="20%"><div align="center">武器名称</div></td>';
        echo '<td width="5%"><div align="center">EN消耗</div></td>';
        echo '<td width="6%"><div align="center">攻击</div></td>';
        echo '<td width="5%"><div align="center">命中</div></td>';
        echo '<td width="5%"><div align="center">回合</div></td>';
        echo '<td width="10%"><div align="center">特效</div></td>';
        echo '<td width="10%"><div align="center">价钱</div></td>';
        echo '<td width="5%"><div align="center">购买</div></td>';
        echo "$weplist</table></table>";
        echo "<p align=left>你的现金: ".number_format($Gen['cash']);
        echo "<br><center><input type=button name=remit disabled value=确定购买 onClick=\"remit.style.visibility='hidden';cfmsell()\"></center></form>";
        }
elseif ($actionb=='remit'){
        
        
        
        $sql = ("SELECT * FROM `".$GLOBALS['DBPrefix']."phpeb_user_bank` WHERE `username` = '$Pl_Value[USERNAME]'");
        $query = mysql_query($sql);
        $BankUser = mysql_fetch_array($query);

        if ($BankUser['status'] != '1'){echo "你还没有在银行开户，不能购买市场中的武器！";postFooter();exit;}
        
  $sql = ("SELECT * FROM `".$GLOBALS['DBPrefix']."phpeb_user_market` WHERE `id` = '$sellid' LIMIT 1;");
  $query = mysql_query($sql);
  $wepnum = mysql_num_rows($query);
  
  if ($wepnum != '1'){echo "该武器已经被其他玩家买走！";postFooter();exit;}
        
        
        $UsrWepA = explode('<!>',$Game['wepa']);
        $UsrWepB = explode('<!>',$Game['wepb']);
        $UsrWepC = explode('<!>',$Game['wepc']);
        if($UsrWepA[0] == '0') {$Game['wepa']=$wepid;$Pos_Flag="购买完成了！你现在正使用这新的武器";}
        elseif($UsrWepB[0] == '0') {$Game['wepb']=$wepid;$Pos_Flag="购买完成了！新的武器存放在备用一";}
        elseif($UsrWepC[0] == '0') {$Game['wepc']=$wepid;$Pos_Flag="购买完成了！新的武器存放在备用二";}
        else {$Pos_Flag="你身上没有空位！本次交易不扣款";$price=0;}
        if ($price>0)
        {
        //给武器
        $sql = ("UPDATE `".$GLOBALS['DBPrefix']."phpeb_user_game_info` SET `wepa` = '$Game[wepa]', `wepb` = '$Game[wepb]', `wepc` = '$Game[wepc]' WHERE `username` = '$Pl_Value[USERNAME]' LIMIT 1;");
        mysql_query($sql);
        //删除商场物品
        $sql = ("DELETE FROM `".$GLOBALS['DBPrefix']."phpeb_user_market` WHERE `id` = '$sellid' LIMIT 1;");
        mysql_query($sql);
        //扣款
        $Gen['cash'] = $Gen['cash'] - $price;
        $sql = ("UPDATE `".$GLOBALS['DBPrefix']."phpeb_user_general_info` SET `cash` = '$Gen[cash]' WHERE `username` = '$Pl_Value[USERNAME]' LIMIT 1;");
        mysql_query($sql);
        //给钱
        GetUsrDetails("$owner",'Gen2','Game2');
        $Gen2['cash'] = $Gen2['cash'] + $price;
        $sql = ("UPDATE `".$GLOBALS['DBPrefix']."phpeb_user_general_info` SET `cash` = '$Gen2[cash]' WHERE `username` = '$owner' LIMIT 1;");
        mysql_query($sql);
  }
        echo "<form action=market.php?actionb=none method=post name=frmeq target=Beta>";
        echo "<p align=center style=\"font-size: 16pt\">$Pos_Flag<br><input type=submit value=\"返回\" onClick=\"parent.Beta.location.replace('gen_info.php')\"><input type=submit value=\"继续逛商场\" onClick=\"frmeq.submit()\"></p>";
        echo "<input type=hidden value='$Pl_Value[USERNAME]' name=Pl_Value[USERNAME]>";
        echo "<input type=hidden value='$Pl_Value[PASSWORD]' name=Pl_Value[PASSWORD]>";
        echo "<input type=hidden name=\"TIMEAUTH\" value=\"$CFU_Time\">";
        echo "</form>";
        }
else {echo "未定义动作！";}
postFooter();exit;
?>