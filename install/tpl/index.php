<?php
$current1 = $current2 = $current3 = $current4 = $current5 = '';
$current1 = 'current';
include_once(INSTALL_DIR."tpl/head.php");
?>
<script type="text/javascript">
function submit_next()
{
	var chk = $("#agree").attr("checked");
	if(!chk || chk == 'undefined')
	{
		$.dialog.alert('要执行安装，点勾选同意本协议');
		return false;
	}
	$.phpok.go("index.php?step=check")
}
</script>
<div class="agreement">
	<div class="txt_box">
    	<p class="fz_16">GNU 较宽松公共许可证 (简体中文翻译版)</p>


<p class="fz_14">声明!</p>

<p>这是一份 GNU 较宽松公共许可证非正式的中文翻译。它不是自由软体基金会所发布，并且不能适用于使用 GNU LGPL 的软体 —— 只有 GNU LGPL 英文原文的版本才行。然而，我们希望这份翻译能帮助中文的使用者更了解 GNU LGPL。</p>

<p>This is an unofficial translation of the GNU Lesser General Public License into Chinese. It was not published by the Free Software Foundation, and does not legally state the distribution terms for software that uses the GNU LGPL--only the original English text of the GNU LGPL does that. However, we hope that this translation will help Chinese speakers understand the GNU LGPL better.</p>

 
<p>GNU 较宽松公共许可证</p>

<p>1999.2, 第 2.1 版</p>

<p>版权所有 (C) 1991, 1999 Free Software Foundation, Inc.</p>

<p>59 Temple Place, Suite 330, Boston, MA 02111-1307 USA</p>

 
<p class="fz_16 col_red">允许每个人复制和发布本授权文件的完整副本，但不允许对它进行任何修改。</p>

 
<p>[这是第一次发表的较宽松公共许可证 (Lesser GPL) 版本。它同时也可视为 GNU 函数库公共许可证 (GNU Library Public License) 第 2 版的后继者，故称为 2.1 版]</p>
	<p class="fz_14">导言</p>

 

<p>大多数软体许可证决意剥夺您共享和修改软体的自由。相反的，GNU 通用公共许可证力图保证您共享和修改自由软体的自由 —— 保证自由软体对所有使用者都是自由的。</p>

 

<p>这个许可证，较宽松公共许可证，适用于一些由自由软体基金会与其他决定使用此许可证的软体作者，所特殊设计的软体套件 —— 象是函数库。您也可以使用它，但我们建议您事先仔细考虑，基于以下的说明是否此许可证或原来的通用公共许可证在任何特殊情况下均为较好的方案。</p>

 

<p>当我们谈到自由软体时，我们所指的是自由，而不是价格。我们的 GNU 通用公共许可证是设计用以确保使您有发布自由软体备份的自由（如果您愿意，您可以对此项服务收取一定的费用）；确保您能收到程式原始码或者在您需要时能得到它；确保您能修改软体或将它的一部分用于新的自由软体；而且还确保您知道您可以做上述的这些事情。</p>

 

<p>为了保护您的权利，我们需要作出限制：禁止任何人否认您上述的权利，或者要求您放弃这些权利。如果您发布软件的副本，或者对之加以修改，这些规定就转化为您的责任。</p>

 

<p>例如，如果您发布此函数库的副本，不管是免费还是收取费用，您必须将您享有的一切权利给予接受者；您必须确保他们也能收到或得到原始程式码；如果您将此函数库与其他的程式码连结，您必须提供完整的目的对象文件和程序(object file)给接受者，则当他们修改此函数库并重新编译过后，可以重新与目的档连结。您并且要将这些条款给他们看，使他们知道他们有这样的权利。</p>

 

<p>我们采取两项措施来保护您的权利: （1）用版权来保护函数库。并且，（2）我们提供您这份许可证，赋予您复制，发布和（或）修改这些函数库的法律许可。</p>

<p>为了保护每个发布者，我们需要非常清楚地让每个人明白，自由函数库是没有担保责任的。如果由于某人修改了函数库，并继续加以传播，我们需要它的接受者明白：他们所得到的并不是原始的版本。故由其他人引入的任何问题，对原作者的声誉将不会有任何的影响。
</p>
    </div>
</div>

<div class="btn_wrap">
	<label class="choose_check"><input id="agree" type="checkbox" />我已阅读并接受该服务条款</label>
    <input name="" type="button" class="next_btn" value="下一步" onclick="submit_next()" />
    <div class="cl"></div>
</div>


<?php include_once(INSTALL_DIR."tpl/foot.php");?>
