<?php
$mode = ( isset($_GET['action']) ) ? $_GET['action'] : $_POST['action'];
include('cfu.php');
postHead('');
AuthUser("$Pl_Value[USERNAME]","$Pl_Value[PASSWORD]");
if ($CFU_Time >= $TIMEAUTH+$TIME_OUT_TIME || $TIMEAUTH <= $CFU_Time-$TIME_OUT_TIME){echo "连线逾时！<br>请重新登入！";exit;}
GetUsrDetails("$Pl_Value[USERNAME]",'Gen','Game');
$t_now = time();
if ($Gen['btltime'] == $t_now){echo "动作过快。";postFooter();mysql_query("UPDATE `".$GLOBALS['DBPrefix']."phpeb_user_general_info` SET `btltime` = ".intval($t_now+10)." WHERE `username` = '$Gen[username]' LIMIT 1;");exit;}
if ($Game['organization'])
$Pl_Org = ReturnOrg("$Game[organization]");

//Set DataTable
$sql = ("SELECT * FROM `".$GLOBALS['DBPrefix']."phpeb_user_bank` WHERE username='". $Pl_Value['USERNAME'] ."'");
$query_bnk = mysql_query($sql);
$defineuserc = 0;
$defineuserc = mysql_num_rows($query_bnk);

if ($defineuserc == 0){
        $sqldfbk = ("INSERT INTO `".$GLOBALS['DBPrefix']."phpeb_user_bank` (username) VALUES('$Pl_Value[USERNAME]')");
        mysql_query($sqldfbk) or die ('<br><center>未能建立银行资料<br>原因:' . mysql_error() . '<br>');
        $sql = ("SELECT * FROM `".$GLOBALS['DBPrefix']."phpeb_user_bank` WHERE username='". $Pl_Value['USERNAME'] ."'");
        $query_bnk = mysql_query($sql) or die ('<br><center>未能建立银行资料<br>原因:' . mysql_error() . '<br>');
}
$Bank = mysql_fetch_array($query_bnk);

//Bank GUI
if ($mode=='main' && $actionb=='none'){
        echo "银行<hr>";
        echo "<br>";
        echo "<form action=bank.php?action=main method=post name=bkmainform>";
        echo "<input type=hidden value='none' name=actionb>";
        echo "<input type=hidden value='$Pl_Value[USERNAME]' name=Pl_Value[USERNAME]>";
        echo "<input type=hidden value='$Pl_Value[PASSWORD]' name=Pl_Value[PASSWORD]>";
        echo "<input type=hidden name=\"TIMEAUTH\" value=\"$CFU_Time\">";
        echo "<script language=\"Javascript\">";
        echo "function ConfirmBanking(){";
        echo "if (bkmainform.actionb.value == 'deposit'){";
        echo "if (bkmainform.d_amount.value > $Gen[cash]){alert('你没有那多钱呢...'+bkmainform.d_amount.value);bkmainform.banking.style.visibility='visible';return false;}";
        echo "else {if (confirm('确定要把 '+bkmainform.d_amount.value+'元 存入银行吗？') == true)";
        echo "{bkmainform.submit();return true}";
        echo "else {bkmainform.banking.style.visibility='visible';}}}";
        echo "else if(bkmainform.actionb.value == 'withdrawl'){";
        echo "if (bkmainform.w_amount.value > $Bank[savings]){alert('你户口裡没有那多钱呢...');bkmainform.banking.style.visibility='visible';return false;}";
        echo "else {if(confirm('确定要把 '+bkmainform.w_amount.value+'元 取出来吗？') == true){bkmainform.submit();return true}";
        echo "else {bkmainform.banking.style.visibility='visible';}}}";
        echo "else if(!bkmainform.actionb.value){alert('你不是想打劫银行吧?');return false;}";
        echo "}function ConfirmAccValid(){";
        echo "if ($Gen[cash] < 1500000){alert('您的所持金不足呢！请加油吧！');bkmainform.AccountValiding.style.visibility='visible';return false;}";
        echo "else if ($Game[level] < 30){alert('您的等不足呢！请加油吧！');bkmainform.AccountValiding.style.visibility='visible';return false;}";
        echo "else {if (confirm('确定要用10万开户吗？') == true){bkmainform.submit();return true}";
        echo "else {bkmainform.AccountValiding.style.visibility='visible';return false;}}";
        echo "}function ConfirmRemit(){";
        echo "if ($Bank[savings] < bkmainform.c_amount.value){alert('您的存款不足呢！');bkmainform.remit.style.visibility='visible';return false;}";
        echo "else if (bkmainform.c_amount.value <= 0){alert('请重新输入金额。');bkmainform.remit.style.visibility='visible';return false;}";
        echo "else if (bkmainform.c_target.value == '0'){alert('请先指定您要汇给谁。');bkmainform.remit.style.visibility='visible';return false;}";
        echo "else {if (confirm('确定要汇 '+bkmainform.c_amount.value+'元吗？') == true){bkmainform.submit();return true}";
        echo "else {bkmainform.remit.style.visibility='visible';return false;}}";
        echo "}function ConfirmBounty(){";
        echo "if ($Bank[savings] < bkmainform.t_amount.value){alert('您的存款不足呢！');bkmainform.bounty.style.visibility='visible';return false;}";
        echo "else if (bkmainform.t_amount.value <= 0){alert('请重新输入金额。');bkmainform.bounty.style.visibility='visible';return false;}";
        echo "else if (bkmainform.t_target.value == '0'){alert('请先指定您要通缉给谁。');bkmainform.bounty.style.visibility='visible';return false;}";
        echo "else {if (confirm('确定要以 '+bkmainform.t_amount.value+'元 通缉吗？') == true){bkmainform.submit();return true}";
        echo "else {bkmainform.bounty.style.visibility='visible';return false;}}";
        echo "}</script>";
        echo "<table align=center border=\"1\" cellpadding=\"0\" cellspacing=\"0\" style=\"border-collapse: collapse;font-size: 10pt;\" bordercolor=\"#FFFFFF\">";
        echo "<tr align=center><td colspan=9><b style=\"font-size: 12        pt;\">银行服务列表: </b></td></tr>";
        if($Bank['status']){
        echo "<tr align=center>";
        echo "<td width=\"100\"><b style=\"font-size: 12pt;\">金融服务</b></td>";
        echo "<td width=\"300\" align=left>您的现金: ".number_format($Gen['cash']);
        echo "<br>您的存款: ".number_format($Bank['savings'])."<hr align=center width=80%>";
        echo "存款 <input type=radio name=actionc value=deposit onClick=\"bkmainform.actionb.value='deposit';banking.disabled=false;c_amount.disabled=true;remit.disabled=true;depamt.disabled=false;whdlamt.disabled=true;t_amount.disabled=true;bounty.disabled=true;\"> : <input id=depamt disabled type=text maxlength=10 name=d_amount value=0>";
        echo "<br>提款 <input type=radio name=actionc value=withdrawl onClick=\"bkmainform.actionb.value='withdrawl';banking.disabled=false;c_amount.disabled=true;remit.disabled=true;whdlamt.disabled=false;depamt.disabled=true;t_amount.disabled=true;bounty.disabled=true;\"> : <input id=whdlamt disabled type=text maxlength=10 name=w_amount value=0>";
        echo "<br><input type=button disabled name=banking value=确定存取 onClick=\"banking.style.visibility='hidden';ConfirmBanking()\">";
        echo "</td>";
        echo "</tr>";
        echo "<tr align=center>";
        echo "<td width=\"100\"><b style=\"font-size: 12pt;\">汇款服务</b></td>";
        echo "<td width=\"300\" align=left>您的存款: ".number_format($Bank['savings'])."<br>";
        echo "把<input type=text disabled name=c_amount value=0 maxlength=10 size=10>元汇给:<br><select name=c_target>";
        echo "<option value='0'>－－－－－〔请选择〕－－－－－";
        if ($Game['organization'])
        echo "<option value='<您所属的组织>' style=\"background: $Pl_Org[color]\">$Pl_Org[name] (组织资金)";
        unset($sql,$query,$BankUsers,$c_rcb);
        $sql = ("SELECT * FROM `".$GLOBALS['DBPrefix']."phpeb_user_bank` WHERE `status` = '1' AND `username` != '$Bank[username]'");
        $query = mysql_query($sql);
        while ($BankUsers = mysql_fetch_array($query)){$c_rcb+=1;
                GetUsrDetails($BankUsers['username'],'','BankGame');
                echo "<option value='$BankGame[username]'>$BankGame[gamename]";
                unset($BankGame);
        }
        unset($sql,$query);
        echo "</select>";
        if (!$c_rcb)$remit_disabledtrue = 'disabled';
        echo "<br>使用汇款服务<input type=radio $remit_disabledtrue name=actionc value=remit onClick=\"bkmainform.actionb.value='remit';c_amount.disabled=false;remit.disabled=false;t_amount.disabled=true;bounty.disabled=true;banking.disabled=true;depamt.disabled=true;whdlamt.disabled=true;\">";
        echo "<br><input type=button name=remit disabled value=确定汇款 onClick=\"remit.style.visibility='hidden';ConfirmRemit()\">";
        echo "</td>";
        echo "</tr>";
        echo "<tr align=center>";
        echo "<td width=\"100\"><b style=\"font-size: 12pt;\">通缉服务</b></td>";
        echo "<td width=\"300\" align=left>您的存款: ".number_format($Bank['savings'])."<br>";
        echo "以<input type=text disabled name=t_amount value=0 maxlength=10 size=10>元通缉:<br><select name=t_target>";
        echo "<option value='0'>－－－－－〔请选择〕－－－－－";
        unset($sql,$query,$BankUsers,$c_rcb);
        $sql = ("SELECT * FROM `".$GLOBALS['DBPrefix']."phpeb_user_general_info` WHERE `username` != '$Bank[username]'");
        $query = mysql_query($sql);
        while ($BntyTrgt = mysql_fetch_array($query)){$c_rcb+=1;
                GetUsrDetails($BntyTrgt['username'],'','BntyTrgtGame');
                echo "<option value='$BntyTrgtGame[username]'>$BntyTrgtGame[gamename]";
                unset($BntyTrgtGame);
        }
        unset($sql,$query);
        echo "</select>";
        if (!$c_rcb)$bounty_disabledtrue = 'disabled';
        echo "<br>使用通缉服务<input type=radio $bounty_disabledtrue name=actionc value=bounty onClick=\"bkmainform.actionb.value='bounty';t_amount.disabled=false;bounty.disabled=false;c_amount.disabled=true;remit.disabled=true;banking.disabled=true;depamt.disabled=true;whdlamt.disabled=true;\">";
        echo "<br><input type=button name=bounty disabled value=确定通缉 onClick=\"bounty.style.visibility='hidden';ConfirmBounty()\">";
        echo "</td>";
        echo "</tr>";
        echo "<tr align=center>";
        echo "<td width=\"100\"><b style=\"font-size: 12pt;\">纪录服务</b></td>";
        echo "<td width=\"300\" align=left>";
        echo "检查银行纪录: ";
        echo "<br><input type=submit value=纪录服务 onClick=\"document.bkmainform.action='bank.php?action=CheckLog&actionb=0';bkmainform.actionb.value='GUI'\">";
        echo "</td>";
        echo "</tr>";
        echo "<tr align=center>";
        echo "<td width=\"100\"><b style=\"font-size: 12pt;\">交易服务</b></td>";
        echo "<td width=\"300\" align=left>";
        echo "麻烦阁下请到保险库部门: ";
        echo "<br><input type=submit value=保险库部门 onClick=\"document.bkmainform.action='bank.php?action=SafeHouse';bkmainform.actionb.value='GUI'\">";
        echo "</td>";
        echo "</tr>";
        }else {
        echo "<input type=hidden value='AccValidation' name=actionc>";
        echo "<tr align=center>";
        echo "<td width=\"100\"><b style=\"font-size: 12pt;\">开户服务</b></td>";
        echo "<td width=\"300\" align=left>任何等级达到".$BankRqLv."级、现金有".ceil($BankRqMoney/10000)."万 (".number_format($BankRqMoney).") 的人也可以开银行户口的。<br>开户会收取您一次性的手续费".ceil($BankFee/10000)."万 (".number_format($BankFee).") ，之后您便可以享受本银行的服务！";
        echo "<br>您的现金: ".number_format($Gen['cash'])."<hr align=center width=80%>";
        echo "<center><input type=submit name=AccountValiding value=确定开户 onClick=\"AccountValiding.style.visibility='hidden';bkmainform.actionb.value='AccValidation';return ConfirmAccValid();\">";
        echo "</td>";
        echo "</tr>";
        }
        echo "</form></table>";
        
}//End GUI
elseif ($mode=='main' && $actionc == $actionb){


$log_amount = $log_tsaving = $log_tcash = $log_amount = $log_type = 0;
$log_target = $log_tg_name = (string) '';

if ($actionb == 'AccValidation'){
        if (1500000 > $Gen['cash']){echo "抱歉, 您的金钱不足。";postFooter();exit;}
        if (30 > $Game['level']){echo "抱歉, 您的等级不足。";postFooter();exit;}
        $Gen['cash'] -= 100000;
        $log_type = 5;
        }
else{
        if($Bank['status'] == '0'){echo "抱歉, 本银行暂时未能提供服务给未开户的人士。";postFooter();exit;}
        if($Bank['status'] == '-1'){echo "抱歉, 阁下的银行帐户暂时被冻结了。";postFooter();exit;}


if ($actionb == 'deposit'){
        if($d_amount>$Gen['cash']){echo "抱歉, 本银行暂时未能提供借款服务, 尤其是借款给客户用来存款的。";postFooter();exit;}
        if($d_amount <= 0){echo "麻烦阁下重新输入金额。";postFooter();exit;}
        $d_amount = intval($d_amount);
        $Gen['cash'] -= $d_amount;
        $Bank['savings'] += $d_amount;
        $log_amount = $d_amount;
        $log_type = 1;
}
elseif ($actionb == 'withdrawl'){
        if($w_amount>$Bank['savings']){echo "抱歉, 本银行暂时未能提供借款服务。";postFooter();exit;}
        if($w_amount <= 0){echo "麻烦阁下重新输入金额。";postFooter();exit;}
        $w_amount = intval($w_amount);
        $Gen['cash'] += $w_amount;
        $Bank['savings'] -= $w_amount;
        $log_amount = $w_amount;
        $log_type = 2;
}
elseif ($actionb == 'remit'){
        $c_amount = intval($c_amount);
        if($c_amount > $Bank['savings']){echo "抱歉, 您的存款不足, 我们无法帮你汇款。";postFooter();exit;}
        if($c_amount <= 0){echo "麻烦阁下重新输入金额。";postFooter();exit;}
        if ($c_target && $c_target != '<您所属的组织>'){
        $sql = ("SELECT `bank`.`status` AS `AcStatus`, `gamename`, `savings`, `cash` FROM `".$GLOBALS['DBPrefix']."phpeb_user_general_info` `gen`,`".$GLOBALS['DBPrefix']."phpeb_user_bank` `bank`,`".$GLOBALS['DBPrefix']."phpeb_user_game_info` `game`  WHERE `bank`.`username` = `game`.`username` AND `bank`.`username` = `gen`.`username` AND `bank`.`username` = '$c_target'");
        $query = mysql_query($sql);
        $BankUser = mysql_fetch_array($query);
        }
        if ($c_target != '<您所属的组织>'){
        if ($BankUser['AcStatus'] != '1'){echo "抱歉, 您目标的人物没有有效的银行帐户。$BankUser[AcStatus]";postFooter();exit;}
        $Bank['savings'] -= $c_amount;
        }
        $log_target = $c_target;
        $log_amount = $c_amount;
        $log_tsaving = $BankUser['savings'] + $c_amount;
        $log_tcash = $BankUser['cash'];
        $log_tg_name = $BankUser['gamename'];
        $log_type = 3;
}
elseif ($actionb == 'bounty'){
        $t_amount = intval($t_amount);
        if($t_amount > $Bank['savings']){echo "抱歉, 您的存款不足, 我们无法帮你汇款通缉。";postFooter();exit;}
        if($t_amount <= 0){echo "麻烦阁下重新输入金额。";postFooter();exit;}
        if($t_target == $Pl_Value['USERNAME']){echo "麻烦阁下不要乱来。";postFooter();exit;}
        $sql = ("SELECT `bounty`,`gamename` FROM `".$GLOBALS['DBPrefix']."phpeb_user_general_info` `gen`,`".$GLOBALS['DBPrefix']."phpeb_user_game_info` `game` WHERE `gen`.`username` = `game`.`username` AND `gen`.`username` = '$t_target'");
        $query = mysql_query($sql) or die(mysql_error());
        $BntyTrgt = mysql_fetch_array($query);
        GetUsrDetails($t_target,'','BntyTrgtGame');
        $Bank['savings'] -= $t_amount;
        $BntyTrgt['bounty'] += $t_amount;
        $log_target = $t_target;
        $log_amount = $t_amount;
        $log_tg_name = $BntyTrgt['gamename'];
        $log_type = 4;
}else{echo "未定义动作！";postFooter();exit;}
}
if ($actionb == 'remit' && $c_target == '<您所属的组织>'){
$Bank['savings'] -= $c_amount;
        unset($sql);
        $sql = ("UPDATE `".$GLOBALS['DBPrefix']."phpeb_user_organization` SET `funds` = `funds`+$c_amount WHERE `id` = '$Game[organization]' LIMIT 1;");
        mysql_query($sql);
}
        unset($sql);
        $sql = ("UPDATE `".$GLOBALS['DBPrefix']."phpeb_user_general_info` SET `cash` = '$Gen[cash]' WHERE `username` = '$Pl_Value[USERNAME]' LIMIT 1;");
        mysql_query($sql);unset($sql);
if ($actionb != 'AccValidation')
$sql = ("UPDATE `".$GLOBALS['DBPrefix']."phpeb_user_bank` SET `savings` = '$Bank[savings]' WHERE `username` = '$Pl_Value[USERNAME]' LIMIT 1;");
else
$sql = ("UPDATE `".$GLOBALS['DBPrefix']."phpeb_user_bank` SET `status` = '1' WHERE `username` = '$Pl_Value[USERNAME]' LIMIT 1;");
mysql_query($sql);unset($sql);
if ($actionb == 'remit' && $c_target != '<您所属的组织>'){
$sql = ("UPDATE `".$GLOBALS['DBPrefix']."phpeb_user_bank` SET `savings` = `savings`+'$c_amount' WHERE `username` = '$c_target' LIMIT 1;");
mysql_query($sql);unset($sql);}
if ($actionb == 'bounty'){
$sql = ("UPDATE `".$GLOBALS['DBPrefix']."phpeb_user_general_info` SET `bounty` = '$BntyTrgt[bounty]' WHERE `username` = '$t_target' LIMIT 1;");
mysql_query($sql);unset($sql);}

$sql = ("INSERT INTO `".$GLOBALS['DBPrefix']."phpeb_user_bank_log` (`time`, `user`, `g_name`, `type`, `amount`, `cash`, `bankamt`, `t_cash`, `t_bankamt`, `target`, `tg_name`) VALUES ('".$CFU_Time."', '$Pl_Value[USERNAME]', '$Game[gamename]', '$log_type', '$log_amount', '$Gen[cash]', '$Bank[savings]', '$log_tcash', '$log_tsaving', '$log_target', '$log_tg_name');");
mysql_query($sql) or die(mysql_error());unset($sql);

        echo "<form action=bank.php?action=main method=post name=frmct target=Beta>";
        echo "<input type=hidden value='none' name=actionb>";
        echo "<input type=hidden value='$Pl_Value[USERNAME]' name=Pl_Value[USERNAME]>";
        echo "<input type=hidden value='$Pl_Value[PASSWORD]' name=Pl_Value[PASSWORD]>";
        echo "<input type=hidden name=\"TIMEAUTH\" value=\"$CFU_Time\">";
        echo "</form>";
        echo "<form action=gmscrn_main.php?action=proc method=post name=frmreturn target=Alpha>";
        echo "<p align=center style=\"font-size: 16pt\">完成！<br><input type=submit value=\"返回\" onClick=\"parent.Beta.location.replace('gen_info.php')\"><input type=submit value=\"继续使用银行\" onClick=\"frmct.submit()\"></p>";
        echo "<input type=hidden value='$Pl_Value[USERNAME]' name=Pl_Value[USERNAME]>";
        echo "<input type=hidden value='$Pl_Value[PASSWORD]' name=Pl_Value[PASSWORD]>";
        echo "<input type=hidden name=\"TIMEAUTH\" value=\"$CFU_Time\">";
        echo "</form>";
}
elseif($mode == 'SafeHouse' && $actionb=='GUI'){
        $Pl_WepB = explode('<!>',$Game['wepb']);
        $Pl_WepC = explode('<!>',$Game['wepc']);
        echo "银行保险库<hr>";
        echo "<br>";
        echo "<form action=bank.php?action=SafeHouse method=post name=bkmainform>";
        echo "<input type=hidden value='none' name=actionb>";
        echo "<input type=hidden value='' name=actionc>";
        echo "<input type=hidden value='$Pl_Value[USERNAME]' name=Pl_Value[USERNAME]>";
        echo "<input type=hidden value='$Pl_Value[PASSWORD]' name=Pl_Value[PASSWORD]>";
        echo "<input type=hidden name=\"TIMEAUTH\" value=\"$CFU_Time\">";
        echo "<script langauge=\"Javascript\">";
        echo "function MakeDeal(){";
        echo "if (confirm('建立这一单交易，可以吗？')==true){bkmainform.actionb.value='MakeDeal';bkmainform.actionc.value= 'none';}";
        echo "else {return false}}";
        echo "function ConfirmDeal(deal){";
        echo "if (confirm('确认这一单交易，可以吗？')==true){bkmainform.actionb.value='PayDeal';bkmainform.actionc.value= deal;}";
        echo "else {return false}}";
        echo "function RejectDeal(deal){";
        echo "if (confirm('拒绝这一单交易，可以吗？')==true){bkmainform.actionb.value='RejectDeal';bkmainform.actionc.value= deal;}";
        echo "else {return false}}";
        echo "function CancelDeal(deal){";
        echo "if (confirm('中止这一单交易，可以吗？')==true){bkmainform.actionb.value='CancelDeal';bkmainform.actionc.value= deal;}";
        echo "else {return false}}</script>";
        echo "<table align=center width=600 border=\"1\" cellpadding=\"0\" cellspacing=\"0\" style=\"border-collapse: collapse;font-size: 10pt;\" bordercolor=\"#FFFFFF\">";
        echo "<tr align=center><td colspan=3><b style=\"font-size: 12pt;\">Inbox: </b></td>";
        echo "</tr><tr valign=top>";
        $EmptyMsg = '<Br><center>没有任何物品</center><Br><Br>';
        echo "<td width=33%><b style=\"font-size: 12pt;\">一号箱: </b><br>";
        if ($Pl_WepB[0] && $Pl_WepC[0])
        $disableOnFull = 'disabled';
        if ($Bank['sh_ina']){
                unset($query,$sql,$SafeIN,$SafeIN_Wep,$SafeIN_Dealer,$D_Specs);
                
                $SafeIN = explode('<#>',$Bank['sh_ina']);
                $SafeIN_Wep = explode('<!>',$SafeIN[2]);
                
                $sql = ("SELECT `gamename`,`name`,`atk`,`hit`,`rd`,`enc`,`w`.`spec`,`equip` FROM `".$GLOBALS['DBPrefix']."phpeb_user_game_info` `g`,`".$GLOBALS['DBPrefix']."phpeb_sys_wep` `w` WHERE `username`='". $SafeIN[0] ."' AND `id` = '". $SafeIN_Wep[0] ."'");
                $query = mysql_query($sql) or die ('无法取得游戏资讯, 原因:' . mysql_error() . '<br>');
                $SafeIN_Dealer = mysql_fetch_array($query);
                        if ($SafeIN_Wep[2]){
                                if ($SafeIN_Wep[2]==1) $SafeIN_Dealer['name'] = $SafeIN_Wep[3].$SafeIN_Dealer['name']."<sub>?</sub>";
                                else $SafeIN_Dealer['name'] = $SafeIN_Dealer['name'].$SafeIN_Wep[3]."<sub>?</sub>";
                                $SafeIN_Dealer['atk'] += $SafeIN_Wep[4];
                                $SafeIN_Dealer['hit'] += $SafeIN_Wep[5];
                                $SafeIN_Dealer['rd'] += $SafeIN_Wep[6];
                                $SafeIN_Dealer['enc'] = $SafeIN_Wep[7];
                        }
                echo "卖家: $SafeIN_Dealer[gamename]<br>出价: ".number_format($SafeIN[1])."<br>装备: $SafeIN_Dealer[name]<br>经验: $SafeIN_Wep[1]<br>能力: <br>";
                echo "　攻击力: $SafeIN_Dealer[atk]　　　回数: $SafeIN_Dealer[rd]<br>　命中: $SafeIN_Dealer[hit]　　　EN消费: $SafeIN_Dealer[enc]<br>";
                $D_Specs = ReturnSpecs($SafeIN_Dealer['spec']);
                echo "特殊效果:";
                if ($SafeIN_Dealer['equip']) echo "可以装备<br>";
                if ($SafeIN_Dealer['spec']) echo $D_Specs;
                elseif(!$SafeIN_Dealer['spec'] && !$SafeIN_Dealer['equip']) echo "没有任何特殊效果<br>";
                echo "<input type=submit $disableOnFull onClick=\"return ConfirmDeal('a')\" value=确认交易>";
                echo "<input type=submit onClick=\"return RejectDeal('a')\" value=拒绝交易>";
        }else
        echo "$EmptyMsg";
        echo "</td>";
        echo "<td width=34%><b style=\"font-size: 12pt;\">二号箱: </b><br>";
        if ($Bank['sh_inb']){
                unset($query,$sql,$SafeIN,$SafeIN_Wep,$SafeIN_Dealer,$D_Specs);
                
                $SafeIN = explode('<#>',$Bank['sh_inb']);
                $SafeIN_Wep = explode('<!>',$SafeIN[2]);
                
                $sql = ("SELECT `gamename`,`name`,`atk`,`hit`,`rd`,`enc`,`w`.`spec`,`equip` FROM `".$GLOBALS['DBPrefix']."phpeb_user_game_info` `g`,`".$GLOBALS['DBPrefix']."phpeb_sys_wep` `w` WHERE `username`='". $SafeIN[0] ."' AND `id` = '". $SafeIN_Wep[0] ."'");
                $query = mysql_query($sql) or die ('无法取得游戏资讯, 原因:' . mysql_error() . '<br>');
                $SafeIN_Dealer = mysql_fetch_array($query);
                        if ($SafeIN_Wep[2]){
                                if ($SafeIN_Wep[2]==1) $SafeIN_Dealer['name'] = $SafeIN_Wep[3].$SafeIN_Dealer['name']."<sub>?</sub>";
                                else $SafeIN_Dealer['name'] = $SafeIN_Dealer['name'].$SafeIN_Wep[3]."<sub>?</sub>";
                                $SafeIN_Dealer['atk'] += $SafeIN_Wep[4];
                                $SafeIN_Dealer['hit'] += $SafeIN_Wep[5];
                                $SafeIN_Dealer['rd'] += $SafeIN_Wep[6];
                                $SafeIN_Dealer['enc'] = $SafeIN_Wep[7];
                        }
                echo "卖家: $SafeIN_Dealer[gamename]<br>出价: ".number_format($SafeIN[1])."<br>装备: $SafeIN_Dealer[name]<br>经验: $SafeIN_Wep[1]<br>能力: <br>";
                echo "　攻击力: $SafeIN_Dealer[atk]　　　回数: $SafeIN_Dealer[rd]<br>　命中: $SafeIN_Dealer[hit]　　　EN消费: $SafeIN_Dealer[enc]<br>";
                $D_Specs = ReturnSpecs($SafeIN_Dealer['spec']);
                echo "特殊效果:";
                if ($SafeIN_Dealer['equip']) echo "可以装备<br>";
                if ($SafeIN_Dealer['spec']) echo $D_Specs;
                else echo "没有任何特殊效果<br>";
                echo "<input type=submit $disableOnFull onClick=\"return ConfirmDeal('b')\" value=确认交易>";
                echo "<input type=submit onClick=\"return RejectDeal('b')\" value=拒绝交易>";
        }else
        echo "$EmptyMsg";
        echo "</td>";
        echo "<td width=33%><b style=\"font-size: 12pt;\">三号箱: </b><br>";
        if ($Bank['sh_inc']){
                unset($query,$sql,$SafeIN,$SafeIN_Wep,$SafeIN_Dealer,$D_Specs);
                
                $SafeIN = explode('<#>',$Bank['sh_inc']);
                $SafeIN_Wep = explode('<!>',$SafeIN[2]);
                
                $sql = ("SELECT `gamename`,`name`,`atk`,`hit`,`rd`,`enc`,`w`.`spec`,`equip` FROM `".$GLOBALS['DBPrefix']."phpeb_user_game_info` `g`,`".$GLOBALS['DBPrefix']."phpeb_sys_wep` `w` WHERE `username`='". $SafeIN[0] ."' AND `id` = '". $SafeIN_Wep[0] ."'");
                $query = mysql_query($sql) or die ('无法取得游戏资讯, 原因:' . mysql_error() . '<br>');
                $SafeIN_Dealer = mysql_fetch_array($query);
                        if ($SafeIN_Wep[2]){
                                if ($SafeIN_Wep[2]==1) $SafeIN_Dealer['name'] = $SafeIN_Wep[3].$SafeIN_Dealer['name']."<sub>?</sub>";
                                else $SafeIN_Dealer['name'] = $SafeIN_Dealer['name'].$SafeIN_Wep[3]."<sub>?</sub>";
                                $SafeIN_Dealer['atk'] += $SafeIN_Wep[4];
                                $SafeIN_Dealer['hit'] += $SafeIN_Wep[5];
                                $SafeIN_Dealer['rd'] += $SafeIN_Wep[6];
                                $SafeIN_Dealer['enc'] = $SafeIN_Wep[7];
                        }
                echo "卖家: $SafeIN_Dealer[gamename]<br>出价: ".number_format($SafeIN[1])."<br>装备: $SafeIN_Dealer[name]<br>经验: $SafeIN_Wep[1]<br>能力: <br>";
                echo "　攻击力: $SafeIN_Dealer[atk]　　　回数: $SafeIN_Dealer[rd]<br>　命中: $SafeIN_Dealer[hit]　　　EN消费: $SafeIN_Dealer[enc]<br>";
                $D_Specs = ReturnSpecs($SafeIN_Dealer['spec']);
                echo "特殊效果:";
                if ($SafeIN_Dealer['equip']) echo "可以装备<br>";
                if ($SafeIN_Dealer['spec']) echo $D_Specs;
                else echo "没有任何特殊效果<br>";
                echo "<input type=submit $disableOnFull onClick=\"return ConfirmDeal('c')\" value=确认交易>";
                echo "<input type=submit onClick=\"return RejectDeal('c')\" value=拒绝交易>";
        }else
        echo "$EmptyMsg";
        echo "</td>";
        echo "</tr>";
        echo "<tr align=center><td colspan=3><b style=\"font-size: 12pt;\">Outbox: </b></td>";
        echo "</tr><tr valign=top>";
        echo "<td width=33%><b style=\"font-size: 12pt;\">一号箱: </b><br>";
        if ($Bank['sh_outa']){
                unset($query,$sql,$SafeOUT,$SafeOUT_Wep,$SafeOUT_Dealer,$D_Specs);
                
                $SafeOUT = explode('<#>',$Bank['sh_outa']);
                $SafeOUT_Wep = explode('<!>',$SafeOUT[2]);
                
                $sql = ("SELECT `gamename`,`name`,`atk`,`hit`,`rd`,`enc`,`w`.`spec`,`equip` FROM `".$GLOBALS['DBPrefix']."phpeb_user_game_info` `g`,`".$GLOBALS['DBPrefix']."phpeb_sys_wep` `w` WHERE `username`='". $SafeOUT[0] ."' AND `id` = '". $SafeOUT_Wep[0] ."'");
                $query = mysql_query($sql) or die ('无法取得游戏资讯, 原因:' . mysql_error() . '<br>');
                $SafeOUT_Dealer = mysql_fetch_array($query);
                        if ($SafeOUT_Wep[2]){
                                if ($SafeOUT_Wep[2]==1) $SafeOUT_Dealer['name'] = $SafeOUT_Wep[3].$SafeOUT_Dealer['name']."<sub>?</sub>";
                                else $SafeOUT_Dealer['name'] = $SafeOUT_Dealer['name'].$SafeOUT_Wep[3]."<sub>?</sub>";
                                $SafeOUT_Dealer['atk'] += $SafeOUT_Wep[4];
                                $SafeOUT_Dealer['hit'] += $SafeOUT_Wep[5];
                                $SafeOUT_Dealer['rd'] += $SafeOUT_Wep[6];
                                $SafeOUT_Dealer['enc'] = $SafeOUT_Wep[7];
                        }
                echo "目标买家: $SafeOUT_Dealer[gamename]<br>售价: ".number_format($SafeOUT[1])."<br>装备: $SafeOUT_Dealer[name]<br>经验: $SafeOUT_Wep[1]<br>能力: <br>";
                echo "　攻击力: $SafeOUT_Dealer[atk]　　　回数: $SafeOUT_Dealer[rd]<br>　命中: $SafeOUT_Dealer[hit]　　　EN消费: $SafeOUT_Dealer[enc]<br>";
                $D_Specs = ReturnSpecs($SafeOUT_Dealer['spec']);
                echo "特殊效果:";
                if ($SafeOUT_Dealer['equip']) echo "可以装备<br>";
                if ($SafeOUT_Dealer['spec']) echo $D_Specs;
                else echo "没有任何特殊效果<br>";
                echo "<input type=submit $disableOnFull onClick=\"return CancelDeal('a')\" value=中止交易>";
        }else
        echo "$EmptyMsg";
        echo "</td>";
        echo "<td width=34%><b style=\"font-size: 12pt;\">二号箱: </b><br>";
        if ($Bank['sh_outb']){
                unset($query,$sql,$SafeOUT,$SafeOUT_Wep,$SafeOUT_Dealer,$D_Specs);
                
                $SafeOUT = explode('<#>',$Bank['sh_outb']);
                $SafeOUT_Wep = explode('<!>',$SafeOUT[2]);
                
                $sql = ("SELECT `gamename`,`name`,`atk`,`hit`,`rd`,`enc`,`w`.`spec`,`equip` FROM `".$GLOBALS['DBPrefix']."phpeb_user_game_info` `g`,`".$GLOBALS['DBPrefix']."phpeb_sys_wep` `w` WHERE `username`='". $SafeOUT[0] ."' AND `id` = '". $SafeOUT_Wep[0] ."'");
                $query = mysql_query($sql) or die ('无法取得游戏资讯, 原因:' . mysql_error() . '<br>');
                $SafeOUT_Dealer = mysql_fetch_array($query);
                        if ($SafeOUT_Wep[2]){
                                if ($SafeOUT_Wep[2]==1) $SafeOUT_Dealer['name'] = $SafeOUT_Wep[3].$SafeOUT_Dealer['name']."<sub>?</sub>";
                                else $SafeOUT_Dealer['name'] = $SafeOUT_Dealer['name'].$SafeOUT_Wep[3]."<sub>?</sub>";
                                $SafeOUT_Dealer['atk'] += $SafeOUT_Wep[4];
                                $SafeOUT_Dealer['hit'] += $SafeOUT_Wep[5];
                                $SafeOUT_Dealer['rd'] += $SafeOUT_Wep[6];
                                $SafeOUT_Dealer['enc'] = $SafeOUT_Wep[7];
                        }
                echo "目标买家: $SafeOUT_Dealer[gamename]<br>售价: ".number_format($SafeOUT[1])."<br>装备: $SafeOUT_Dealer[name]<br>经验: $SafeOUT_Wep[1]<br>能力: <br>";
                echo "　攻击力: $SafeOUT_Dealer[atk]　　　回数: $SafeOUT_Dealer[rd]<br>　命中: $SafeOUT_Dealer[hit]　　　EN消费: $SafeOUT_Dealer[enc]<br>";
                $D_Specs = ReturnSpecs($SafeOUT_Dealer['spec']);
                echo "特殊效果:";
                if ($SafeOUT_Dealer['equip']) echo "可以装备<br>";
                if ($SafeOUT_Dealer['spec']) echo $D_Specs;
                else echo "没有任何特殊效果<br>";
                echo "<input type=submit $disableOnFull onClick=\"return CancelDeal('b')\" value=中止交易>";
        }else
        echo "$EmptyMsg";
        echo "</td>";
        echo "<td width=33%><b style=\"font-size: 12pt;\">三号箱: </b><br>";
        if ($Bank['sh_outc']){
                unset($query,$sql,$SafeOUT,$SafeOUT_Wep,$SafeOUT_Dealer,$D_Specs);
                
                $SafeOUT = explode('<#>',$Bank['sh_outc']);
                $SafeOUT_Wep = explode('<!>',$SafeOUT[2]);
                
                $sql = ("SELECT `gamename`,`name`,`atk`,`hit`,`rd`,`enc`,`w`.`spec`,`equip` FROM `".$GLOBALS['DBPrefix']."phpeb_user_game_info` `g`,`".$GLOBALS['DBPrefix']."phpeb_sys_wep` `w` WHERE `username`='". $SafeOUT[0] ."' AND `id` = '". $SafeOUT_Wep[0] ."'");
                $query = mysql_query($sql) or die ('无法取得游戏资讯, 原因:' . mysql_error() . '<br>');
                $SafeOUT_Dealer = mysql_fetch_array($query);
                        if ($SafeOUT_Wep[2]){
                                if ($SafeOUT_Wep[2]==1) $SafeOUT_Dealer['name'] = $SafeOUT_Wep[3].$SafeOUT_Dealer['name']."<sub>?</sub>";
                                else $SafeOUT_Dealer['name'] = $SafeOUT_Dealer['name'].$SafeOUT_Wep[3]."<sub>?</sub>";
                                $SafeOUT_Dealer['atk'] += $SafeOUT_Wep[4];
                                $SafeOUT_Dealer['hit'] += $SafeOUT_Wep[5];
                                $SafeOUT_Dealer['rd'] += $SafeOUT_Wep[6];
                                $SafeOUT_Dealer['enc'] = $SafeOUT_Wep[7];
                        }
                echo "目标买家: $SafeOUT_Dealer[gamename]<br>售价: ".number_format($SafeOUT[1])."<br>装备: $SafeOUT_Dealer[name]<br>经验: $SafeOUT_Wep[1]<br>能力: <br>";
                echo "　攻击力: $SafeOUT_Dealer[atk]　　　回数: $SafeOUT_Dealer[rd]<br>　命中: $SafeOUT_Dealer[hit]　　　EN消费: $SafeOUT_Dealer[enc]<br>";
                $D_Specs = ReturnSpecs($SafeOUT_Dealer['spec']);
                echo "特殊效果:";
                if ($SafeOUT_Dealer['equip']) echo "可以装备<br>";
                if ($SafeOUT_Dealer['spec']) echo $D_Specs;
                else echo "没有任何特殊效果<br>";
                echo "<input type=submit $disableOnFull onClick=\"return CancelDeal('c')\" value=中止交易>";
        }else
        echo "$EmptyMsg";
        echo "</td>";
        echo "</tr>";
        echo "</table>";
        echo "<hr width=80% style=\"filter:alpha(opacity=100,finishopacity=40,style=2)\"><br>";
        echo "<table align=center border=\"1\" cellpadding=\"0\" cellspacing=\"0\" style=\"border-collapse: collapse;font-size: 10pt;\" bordercolor=\"#FFFFFF\">";
        echo "<tr><td align=left width=250><b style=\"font-size: 10pt;\">建立交易: </b></td></tr>";
        echo "<tr><td align=left><b>把</b><br>";
        if ($Pl_WepB[0]){
        GetWeaponDetails($Pl_WepB[0],'Pl_WepBS');
        if ($Pl_WepB[2]){
        if ($Pl_WepB[2]==1) $Pl_WepBS['name'] = $Pl_WepB[3].$Pl_WepBS['name']."<sub>?</sub>";
        else $Pl_WepBS['name'] = $Pl_WepBS['name'].$Pl_WepB[3]."<sub>?</sub>";
        $Pl_WepBS['atk'] += $Pl_WepB[4];
        $Pl_WepBS['hit'] += $Pl_WepB[5];
        $Pl_WepBS['rd'] += $Pl_WepB[6];
        $Pl_WepBS['enc'] = $Pl_WepB[7];
        }

        echo "　备用武器一:<br>　　".$Pl_WepBS['name']." <input type=radio value='wepb' name=sellslot>";
        }
        if ($Pl_WepB[0] && $Pl_WepC[0])
        echo "<br>";
        if ($Pl_WepC[0]){
        GetWeaponDetails($Pl_WepC[0],'Pl_WepCS');
        if ($Pl_WepC[2]){
        if ($Pl_WepC[2]==1) $Pl_WepCS['name'] = $Pl_WepC[3].$Pl_WepCS['name']."<sub>?</sub>";
        else $Pl_WepCS['name'] = $Pl_WepCS['name'].$Pl_WepC[3]."<sub>?</sub>";
        $Pl_WepCS['atk'] += $Pl_WepC[4];
        $Pl_WepCS['hit'] += $Pl_WepC[5];
        $Pl_WepCS['rd'] += $Pl_WepC[6];
        $Pl_WepCS['enc'] = $Pl_WepC[7];
        }
        echo "　备用武器二:<br>　　".$Pl_WepCS['name']." <input type=radio value='wepc' name=sellslot>";
        }
        echo "<br><b>卖给</b><br>";
        
        unset($sql,$query,$BankUsers,$c_rcb);
        $Exch_Avail = '';
        $sql = ("SELECT * FROM `".$GLOBALS['DBPrefix']."phpeb_user_bank` WHERE `status` = '1' AND `username` != '$Bank[username]' AND (!`sh_ina` OR !`sh_inb` OR !`sh_inc`)");
        $query = mysql_query($sql);
        while ($BankUsers = mysql_fetch_array($query)){$c_rcb+=1;
                GetUsrDetails($BankUsers['username'],'','BankGame');
                $Exch_Avail .= "<option value='$BankGame[username]'>$BankGame[gamename]";
                unset($BankGame);
        }
        unset($sql,$query);
        if (!$c_rcb)$exch_disabledtrue = 'disabled';
        echo "<select name=exch_target $exch_disabledtrue>";
        echo "<option value='0'>－－－－－〔请选择〕－－－－－";
        echo "$Exch_Avail";
        echo "</select>";
        echo "<br><b>出价:</b><br><input type=text name=price_sell maxlength=10 size=10><br>";
        echo "<input type=submit onClick=\"return MakeDeal()\" value=建立交易>";
        echo "</td></tr>";
        echo "</table>";
        echo "</form>";
}

elseif($mode == 'SafeHouse' && $actionb && $actionc){
        $Pl_WepB = explode('<!>',$Game['wepb']);
        $Pl_WepC = explode('<!>',$Game['wepc']);
        
        if ($actionb=='PayDeal'){
        
        if (!$Pl_WepB[0]){$FreeSlot = 'wepb';}
        elseif (!$Pl_WepC[0]){$FreeSlot = 'wepc';}
        else {echo "<center>您身上装备的已满了，无法完全交易。";postFooter();exit;}

        if ($actionc=='a'){$SafeIN = explode('<#>',$Bank['sh_ina']);$InS = 'sh_ina';}
        elseif ($actionc=='b'){$SafeIN = explode('<#>',$Bank['sh_inb']);$InS = 'sh_inb';}
        elseif ($actionc=='c'){$SafeIN = explode('<#>',$Bank['sh_inc']);$InS = 'sh_inc';}
        else {echo "抱歉, 未能找到需要的资讯。";postFooter();exit;}
        
        if ($Bank['savings'] < $SafeIN[1]){echo "<center>您的存款不足，无法完全交易。";postFooter();exit;}
        if (!$SafeIN || !$SafeIN[0] || !$SafeIN[1] || !$SafeIN[2] || !$SafeIN[3])
        {echo "<center>抱歉, 未能找到需要的资讯。";postFooter();exit;}

                $sql = ("SELECT `bank`.`status` AS `status`,`$SafeIN[3]`,`gamename`,`savings`,`cash` FROM `".$GLOBALS['DBPrefix']."phpeb_user_bank` `bank`, `".$GLOBALS['DBPrefix']."phpeb_user_game_info` `game`, `".$GLOBALS['DBPrefix']."phpeb_user_general_info` `gen` WHERE `game`.`username`= `bank`.`username` AND `gen`.`username`= `bank`.`username` AND `bank`.`username`='". $SafeIN[0] ."'");
                $query = mysql_query($sql) or die ('无法取得游戏资讯, 原因:' . mysql_error() . '<br>');
                $SafeIN_Dealer = mysql_fetch_array($query);
        
        if (!$SafeIN_Dealer[0] || !$SafeIN_Dealer[1]){echo "<center>抱歉, 未能找到需要的资讯。";postFooter();exit;}
        else $DealerOUT = explode('<#>',$SafeIN_Dealer[1]);
        
        if ($DealerOUT[0] != $Game['username']){echo "<center>抱歉, 对方表示此物品不是售给您的。";postFooter();exit;}
        if ($DealerOUT[1] != $SafeIN[1]){echo "<center>抱歉, 对方表示此价钱无法完成交易。";postFooter();exit;}
        if ($DealerOUT[2] != $SafeIN[2]){echo "<center>抱歉, 对方表示不是出售此物品。";postFooter();exit;}
        if ($DealerOUT[3] != $InS){echo "<center>抱歉, 保险库的位置出错。";postFooter();exit;}
        
        //Input Data
        
        unset($sql);
        $sql =        ("UPDATE `".$GLOBALS['DBPrefix']."phpeb_user_bank` SET `savings` = `savings`-".$SafeIN[1].", `$InS` = '' WHERE `username` = '$Pl_Value[USERNAME]' LIMIT 1;");
        mysql_query($sql) or die ('无法取得游戏资讯, 原因:' . mysql_error() . '<br>');unset($sql);
        $sql =        ("UPDATE `".$GLOBALS['DBPrefix']."phpeb_user_bank` SET `savings` = `savings`+".$DealerOUT[1].", `$SafeIN[3]` = '' WHERE `username` = '$SafeIN[0]' LIMIT 1;");
        mysql_query($sql) or die ('无法取得游戏资讯, 原因:' . mysql_error() . '<br>');unset($sql);
        $sql =        ("UPDATE `".$GLOBALS['DBPrefix']."phpeb_user_game_info` SET `$FreeSlot` = '".$SafeIN[2]."' WHERE `username` = '$Game[username]' LIMIT 1;");
        mysql_query($sql) or die ('无法取得游戏资讯, 原因:' . mysql_error() . '<br>');unset($sql);
        $sql = ("INSERT INTO `".$GLOBALS['DBPrefix']."phpeb_user_bank_log` (`time`, `user`, `g_name`, `type`, `amount`, `cash`, `bankamt`, `t_cash`, `t_bankamt`, `target`, `tg_name`, `safehouse`) VALUES ('".$CFU_Time."', '$Pl_Value[USERNAME]', '$Game[gamename]', '6', '$SafeIN[1]', '$Gen[cash]', '".intval($Bank['savings']-$SafeIN[1])."', '$SafeIN_Dealer[cash]', '".intval($SafeIN_Dealer['savings']+$SafeIN[1])."', '$SafeIN[0]', '$SafeIN_Dealer[gamename]', '$Bank[$InS]');");
        mysql_query($sql) or die ('无法取得游戏资讯, 原因:' . mysql_error() . '<br>');unset($sql);
        
        $Message = "交易顺利完成！";
        
        }
        
        elseif ($actionb=='CancelDeal'){
                
        if ($actionc=='a'){$SafeOUT = explode('<#>',$Bank['sh_outa']);$OutS = 'sh_outa';}
        elseif ($actionc=='b'){$SafeOUT = explode('<#>',$Bank['sh_outb']);$OutS = 'sh_outb';}
        elseif ($actionc=='c'){$SafeOUT = explode('<#>',$Bank['sh_outc']);$OutS = 'sh_outc';}
        else {echo "抱歉, 未能找到需要的资讯。";postFooter();exit;}
        
        if (!$SafeOUT || !$SafeOUT[0] || !$SafeOUT[1] || !$SafeOUT[2] || !$SafeOUT[3])
        {echo "<center>抱歉, 未能找到需要的资讯。";postFooter();exit;}


        if (!$Pl_WepB[0]){$FreeSlot = 'wepb';}
        elseif (!$Pl_WepC[0]){$FreeSlot = 'wepc';}
        else {echo "<center>您身上装备的已满了，无法中止交易。";postFooter();exit;}

                $sql = ("SELECT `gamename` FROM `".$GLOBALS['DBPrefix']."phpeb_user_game_info` WHERE `username`='". $SafeOUT[0] ."'");
                $query = mysql_query($sql) or die ('无法取得游戏资讯, 原因:' . mysql_error() . '<br>');
                $SafeOUT_Dealer = mysql_fetch_array($query);

        //Input Data
        
        unset($sql);
        $sql =        ("UPDATE `".$GLOBALS['DBPrefix']."phpeb_user_bank` SET `$OutS` = '' WHERE `username` = '$Pl_Value[USERNAME]' LIMIT 1;");
        mysql_query($sql) or die ('无法取得游戏资讯, 原因:' . mysql_error() . '<br>');unset($sql);
        $sql =        ("UPDATE `".$GLOBALS['DBPrefix']."phpeb_user_bank` SET  `$SafeOUT[3]` = '' WHERE `username` = '$SafeOUT[0]' LIMIT 1;");
        mysql_query($sql) or die ('无法取得游戏资讯, 原因:' . mysql_error() . '<br>');unset($sql);
        $sql =        ("UPDATE `".$GLOBALS['DBPrefix']."phpeb_user_game_info` SET `$FreeSlot` = '".$SafeOUT[2]."' WHERE `username` = '$Game[username]' LIMIT 1;");
        mysql_query($sql) or die ('无法取得游戏资讯, 原因:' . mysql_error() . '<br>');unset($sql);
        $sql = ("INSERT INTO `".$GLOBALS['DBPrefix']."phpeb_user_bank_log` (`time`, `user`, `g_name`, `type`, `target`, `tg_name`) VALUES ('".$CFU_Time."', '$Pl_Value[USERNAME]', '$Game[gamename]', '7', '$SafeOUT[0]', '$SafeOUT_Dealer[gamename]');");
        mysql_query($sql) or die ('无法取得游戏资讯, 原因:' . mysql_error() . '<br>');unset($sql);
        $Message = "中止交易完成！";
        }
        
        elseif ($actionb=='RejectDeal'){
        if ($actionc=='a'){$SafeIN = explode('<#>',$Bank['sh_ina']);$InS = 'sh_ina';}
        elseif ($actionc=='b'){$SafeIN = explode('<#>',$Bank['sh_inb']);$InS = 'sh_inb';}
        elseif ($actionc=='c'){$SafeIN = explode('<#>',$Bank['sh_inc']);$InS = 'sh_inc';}
        else {echo "抱歉, 未能找到需要的资讯。";postFooter();exit;}
        
        if (!$SafeIN || !$SafeIN[0] || !$SafeIN[1] || !$SafeIN[2] || !$SafeIN[3])
        {echo "<center>抱歉, 未能找到需要的资讯。";postFooter();exit;}

                $sql = ("SELECT `gamename` FROM `".$GLOBALS['DBPrefix']."phpeb_user_game_info` WHERE `username`='". $SafeIN[0] ."'");
                $query = mysql_query($sql) or die ('无法取得游戏资讯, 原因:' . mysql_error() . '<br>');
                $SafeIN_Dealer = mysql_fetch_array($query);

        //Input Data
        
        unset($sql);
        $sql =        ("UPDATE `".$GLOBALS['DBPrefix']."phpeb_user_bank` SET `$InS` = '' WHERE `username` = '$Pl_Value[USERNAME]' LIMIT 1;");
        mysql_query($sql) or die ('无法取得游戏资讯, 原因:' . mysql_error() . '<br>');unset($sql);
        $sql = ("INSERT INTO `".$GLOBALS['DBPrefix']."phpeb_user_bank_log` (`time`, `user`, `g_name`, `type`, `target`, `tg_name`) VALUES ('".$CFU_Time."', '$Pl_Value[USERNAME]', '$Game[gamename]', '8', '$SafeIN[0]', '$SafeIN_Dealer[gamename]');");
        mysql_query($sql) or die ('无法取得游戏资讯, 原因:' . mysql_error() . '<br>');unset($sql);
        
        $Message = "拒绝交易完成！";
        }
        
        elseif($actionb=='MakeDeal'){
        $price_sell = intval($price_sell);
        if ($sellslot !='wepb' && $sellslot !='wepc'){echo "抱歉, 未能找到需要的资讯。";postFooter();exit;}
        if ($price_sell <= 0){echo "抱歉, 价钱不能是零或负数。";postFooter();exit;}
        if (!$exch_target){echo "麻烦请先输入目标人物，多谢合作。";postFooter();exit;}
        if ($exch_target == $Pl_Value['USERNAME']){echo "麻烦不要在这捣乱，多谢合作。";postFooter();exit;}
        
        $sql = ("SELECT `bank`.`status` AS `status`, `sh_ina`, `sh_inb`, `sh_inc`, `gamename`, `savings`, `cash` FROM `".$GLOBALS['DBPrefix']."phpeb_user_bank` `bank`, `".$GLOBALS['DBPrefix']."phpeb_user_game_info` `game`, `".$GLOBALS['DBPrefix']."phpeb_user_general_info` `gen` WHERE `game`.`username`= `bank`.`username` AND `gen`.`username`= `bank`.`username` AND `bank`.`username`='$exch_target'");
        $query = mysql_query($sql) or die ('无法取得游戏资讯, 原因:' . mysql_error() . '<br>');
        $BankUser = mysql_fetch_array($query);

        if ($BankUser['status'] != '1'){echo "抱歉, 您目标的人物没有有效的银行帐户。";postFooter();exit;}
        
        unset($PlaceSlotIN,$PlaceSlotOUT);
        if (!$BankUser['sh_ina']){$PlaceSlotIN = 'sh_ina';}
        elseif (!$BankUser['sh_inb']){$PlaceSlotIN = 'sh_inb';}
        elseif (!$BankUser['sh_inc']){$PlaceSlotIN = 'sh_inc';}
        else {echo "抱歉, 您目标的人物保险库的没有空间了。";postFooter();exit;}
        
        if (!$Bank['sh_outa']){$PlaceSlotOUT = 'sh_outa';}
        elseif (!$Bank['sh_outb']){$PlaceSlotOUT = 'sh_outb';}
        elseif (!$Bank['sh_outc']){$PlaceSlotOUT = 'sh_outc';}
        else {echo "抱歉, 您只能同时建立三单交易。";postFooter();exit;}

        //Input Data
        unset($sql);
        $sql =        ("UPDATE `".$GLOBALS['DBPrefix']."phpeb_user_bank` SET `$PlaceSlotOUT` = '".$exch_target."<#>".$price_sell."<#>".$Game[$sellslot]."<#>$PlaceSlotIN' WHERE `username` = '$Pl_Value[USERNAME]' LIMIT 1;");
        mysql_query($sql) or die ('无法取得游戏资讯, 原因:' . mysql_error() . '<br>');unset($sql);
        $sql =        ("UPDATE `".$GLOBALS['DBPrefix']."phpeb_user_bank` SET  `$PlaceSlotIN` = '".$Pl_Value['USERNAME']."<#>".$price_sell."<#>".$Game[$sellslot]."<#>$PlaceSlotOUT' WHERE `username` = '$exch_target' LIMIT 1;");
        mysql_query($sql) or die ('无法取得游戏资讯, 原因:' . mysql_error() . '<br>');unset($sql);
        $sql =        ("UPDATE `".$GLOBALS['DBPrefix']."phpeb_user_game_info` SET  `$sellslot` = '0<!>0' WHERE `username` = '$Pl_Value[USERNAME]' LIMIT 1;");
        mysql_query($sql) or die ('无法取得游戏资讯, 原因:' . mysql_error() . '<br>');unset($sql);
        $sql = ("INSERT INTO `".$GLOBALS['DBPrefix']."phpeb_user_bank_log` (`time`, `user`, `g_name`, `type`, `target`, `tg_name`) VALUES ('".$CFU_Time."', '$Pl_Value[USERNAME]', '$Game[gamename]', '9', '$exch_target', '$BankUser[gamename]');");
        mysql_query($sql) or die ('无法取得游戏资讯, 原因:' . mysql_error() . '<br>');unset($sql);
        $Message = "完成建立交易！";
                }
        else  {$Message = "未定义动作！";}


        echo "<form action=bank.php?action=SafeHouse method=post name=frmct target=Beta>";
        echo "<input type=hidden value='GUI' name=actionb>";
        echo "<input type=hidden value='$Pl_Value[USERNAME]' name=Pl_Value[USERNAME]>";
        echo "<input type=hidden value='$Pl_Value[PASSWORD]' name=Pl_Value[PASSWORD]>";
        echo "<input type=hidden name=\"TIMEAUTH\" value=\"$CFU_Time\">";
        echo "</form>";
        echo "<form action=gmscrn_main.php?action=proc method=post name=frmreturn target=Alpha>";
        echo "<p align=center style=\"font-size: 16pt\">$Message<br><input type=submit value=\"返回\" onClick=\"parent.Beta.location.replace('gen_info.php')\"><input type=submit value=\"继续使用保险库\" onClick=\"frmct.submit()\"></p>";
        echo "<input type=hidden value='$Pl_Value[USERNAME]' name=Pl_Value[USERNAME]>";
        echo "<input type=hidden value='$Pl_Value[PASSWORD]' name=Pl_Value[PASSWORD]>";
        echo "<input type=hidden name=\"TIMEAUTH\" value=\"$CFU_Time\">";
        echo "</form>";
        
}
elseif($mode == 'CheckLog' && $actionb >= 0){
        echo "银行日志纪录<hr>";
        echo "<br>";

        $L_Lim = intval($actionb)*$Bank_SLog_Entries;
        $U_Lim = $L_Lim + $Bank_SLog_Entries;
        
        if ($Gen['acc_status'] >= 0) $SQL = ("SELECT `id`, `time`, `user`, `g_name`, `type`, `amount`, `cash`, `bankamt`, `t_cash`, `t_bankamt`, `target`, `tg_name` FROM `".$GLOBALS['DBPrefix']."phpeb_user_bank_log` WHERE `user` = '$Pl_Value[USERNAME]' OR (`target` = '$Pl_Value[USERNAME]' AND `type` != 4) ORDER BY `time` DESC Limit $L_Lim,$U_Lim");
        else $SQL = ("SELECT `id`, `time`, `user`, `g_name`, `type`, `amount`, `cash`, `bankamt`, `t_cash`, `t_bankamt`, `target`, `tg_name` FROM `".$GLOBALS['DBPrefix']."phpeb_user_bank_log` ORDER BY `time` DESC Limit $L_Lim,$U_Lim");
        
        $SQL_Query = mysql_query($SQL) or die(mysql_error());
        
        echo "<table align=center border=\"1\" cellpadding=\"0\" cellspacing=\"0\" style=\"border-collapse: collapse;font-size: 10pt;\" bordercolor=\"#FFFFFF\">";
        
        echo "<form action=bank.php?action=CheckLog method=post name=frmcl target=Beta>";
        echo "<input type=hidden value='".intval($actionb+1)."' name=actionb>";
        echo "<input type=hidden value='' name=log_id>";
        echo "<input type=hidden value='$Pl_Value[USERNAME]' name=Pl_Value[USERNAME]>";
        echo "<input type=hidden value='$Pl_Value[PASSWORD]' name=Pl_Value[PASSWORD]>";
        echo "<input type=hidden name=\"TIMEAUTH\" value=\"$CFU_Time\">";

        echo "<tr align=center><td width=225>日期时间</td>";
        echo "<td width=150>支付者</td>";
        echo "<td width=70>动作</td>";
        echo "<td width=150>金额 ($)</td>";
        echo "<td width=140>现金结餘 ($)</td>";
        echo "<td width=140>帐户结餘 ($)</td>";
        echo "<td width=150>对象帐号</td>";
        echo "</tr>";
        
        function TypeBanking($act){
                switch($act){
                        case 1: $ActionL = '存款';break;
                        case 2: $ActionL = '提款';break;
                        case 3: $ActionL = '汇款';break;
                        case 4: $ActionL = '通缉';break;
                        case 5: $ActionL = '开户';break;
                        case 6: $ActionL = '完成交易';break;
                        case 7: $ActionL = '取消交易';break;
                        case 8: $ActionL = '拒绝交易';break;
                        case 9: $ActionL = '提出交易';break;
                        default : $ActionL = '未指定';break;
                }
        return $ActionL;
        }

while($Logs = mysql_fetch_array($SQL_Query)){
        echo "<tr align=center><td>".cfu_time_convert($Logs['time'])."</td>";
        if ($Gen['acc_status'] < 0) echo "<td>$Logs[g_name]<br>($Logs[user])</td>";
        else echo "<td>$Logs[g_name]</td>";
        if ($Logs['type'] == 6)
        echo "<td style=\"cursor: hand\" onClick=\"frmcl.action='bank.php?action=CheckSHLog';frmcl.log_id.value=$Logs[id];frmcl.submit();\">".TypeBanking($Logs['type'])."</td>";
        else        
        echo "<td>".TypeBanking($Logs['type'])."</td>";
        if (($Logs['type'] >= 7 && $Logs['type'] <= 9))
        echo "<td>N/A</td>";        
        else
        echo "<td>".number_format($Logs['amount'])."</td>";
        if (($Logs['type'] == 3 || $Logs['type'] == 6) && $Logs['target'] == $Pl_Value['USERNAME']){
                echo "<td>".number_format($Logs['t_cash'])."</td>";
                echo "<td>".number_format($Logs['t_bankamt'])."</td>";
        }
        elseif ($Logs['type'] >= 7 && $Logs['type'] <= 9){
                echo "<td>N/A</td>";
                echo "<td>N/A</td>";
        }
        else {
                echo "<td>".number_format($Logs['cash'])."</td>";
                echo "<td>".number_format($Logs['bankamt'])."</td>";
        }
        if ($Logs['type'] >= 3) {
                        if ($Gen['acc_status'] < 0) echo "<td>$Logs[tg_name]<br>($Logs[target])</td>";
                        else echo "<td>$Logs[tg_name]</td>";
        }
        else echo "<td>N/A</td>";
        echo "</tr>";        
}
        echo "<tr align=center>";
        echo "<td colspan=7>";
        if (intval($actionb > 0))
        echo "<input type=button value=\"上一页\" onClick=\"frmcl.actionb.value='".intval($actionb-1)."';frmcl.submit();\">";
        echo "第<input type=text maxlength=2 style=\"font-size: 10pt; color: #ffffff; background-color: #000000;text-align: center\" size=2 value='".intval($actionb+1)."' onChange=\"frmcl.actionb.value=Math.round(this.value-1);frmcl.submit();\">页";        
        echo "<input type=button value=\"下一页\" onClick=\"frmcl.submit();\">";
        echo "</td>";
        echo "</tr>";
        echo "</table>";
        echo "</form>";
}

elseif($mode == 'CheckSHLog' && isset($log_id)){
        echo "银行保险库交易项目纪录<hr>";
        echo "<br>";
        $log_id = intval($log_id);
        
                unset($query,$sql,$SafeIN,$SafeIN_Wep,$SafeIN_Dealer,$D_Specs);
                $sql = ("SELECT `g_name`,`tg_name`,`safehouse` FROM `".$GLOBALS['DBPrefix']."phpeb_user_bank_log` WHERE `id` = '".$log_id."'");
                $query = mysql_query($sql) or die ('无法取得游戏资讯, 原因:' . mysql_error() . '<br>');
                $SafeInf = mysql_fetch_array($query);
                
                $SafeIN = explode('<#>',$SafeInf['safehouse']);
                $SafeIN_Wep = explode('<!>',$SafeIN[2]);
                                
                $sql = ("SELECT `name`,`atk`,`hit`,`rd`,`enc`,`spec`,`equip` FROM `".$GLOBALS['DBPrefix']."phpeb_sys_wep` WHERE `id` = '". $SafeIN_Wep[0] ."'");
                $query = mysql_query($sql) or die ('无法取得游戏资讯, 原因:' . mysql_error() . '<br>');
                $SafeIN_Dealer = mysql_fetch_array($query);
                        if ($SafeIN_Wep[2]){
                                if ($SafeIN_Wep[2]==1) $SafeIN_Dealer['name'] = $SafeIN_Wep[3].$SafeIN_Dealer['name']."<sub>?</sub>";
                                else $SafeIN_Dealer['name'] = $SafeIN_Dealer['name'].$SafeIN_Wep[3]."<sub>?</sub>";
                                $SafeIN_Dealer['atk'] += $SafeIN_Wep[4];
                                $SafeIN_Dealer['hit'] += $SafeIN_Wep[5];
                                $SafeIN_Dealer['rd'] += $SafeIN_Wep[6];
                                $SafeIN_Dealer['enc'] = $SafeIN_Wep[7];
                        }
                echo "<table align=center width=200 border=\"1\" cellpadding=\"0\" cellspacing=\"0\" style=\"border-collapse: collapse;font-size: 10pt;\" bordercolor=\"#FFFFFF\">";
                echo "<tr align=center><td colspan=3><b style=\"font-size: 12pt;\">买卖内容: </b></td>";
                echo "</tr><tr valign=top><td>";
        
                echo "卖家: $SafeInf[tg_name]<br>买家: $SafeInf[g_name]<br>出价: ".number_format($SafeIN[1])."<br>装备: $SafeIN_Dealer[name]<br>经验: $SafeIN_Wep[1]<br>能力: <br>";
                echo "　攻击力: $SafeIN_Dealer[atk]　　　回数: $SafeIN_Dealer[rd]<br>　命中: $SafeIN_Dealer[hit]　　　EN消费: $SafeIN_Dealer[enc]<br>";
                $D_Specs = ReturnSpecs($SafeIN_Dealer['spec']);
                echo "特殊效果:";
                if ($SafeIN_Dealer['equip']) echo "可以装备<br>";
                if ($SafeIN_Dealer['spec']) echo $D_Specs;
                elseif(!$SafeIN_Dealer['spec'] && !$SafeIN_Dealer['equip']) echo "没有任何特殊效果<br>";
                echo "</td></tr>";
                echo "</table>";
}


else {echo "未定义动作！";}
postFooter();exit;
?>