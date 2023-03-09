
	var k=0;
	window.onscroll=function(){
		/*if(scrollBotom()){
			var oParent=document.getElementById('main');
			var oBox =document.createElement('div');
			oBox.className='box';
			oParent.appendChild(oBox);
			var oPic =document.createElement('div');
			oPic.className='pic';
			oBox.appendChild(oPic);
			var oImg =document.createElement('img');
			oImg.src='./img/'+k+'.jpg';
			oPic.appendChild(oImg);
			k=k%97;k++;
       		waterFall('main','box');
		}*/
	}
	var myMain=document.getElementById('main');
	var myBox=getByClass(myMain,'box');
	var myBoxW=myBox[0].offsetWidth;
	window.onresize= function(){
		var myNum=Math.floor(document.documentElement.clientWidth/myBoxW);//窗口改变大小时计算一行能存几个Box
		waterFall('main','box',myNum);
	}
	function waterFall(oParent,box){
		var oParent=document.getElementById(oParent);
		var aBox=getByClass(oParent,box);
		var oBoxW=aBox[0].offsetWidth;//一个BOX的宽度
		var boxNum=Math.floor(document.documentElement.clientWidth/oBoxW);//看浏览器的宽度，一行能放下几个box
		if(arguments[2]){//判断参数集合第三个参数是否存在
			boxNum=arguments[2];
		}
		oParent.style.cssText='width:'+oBoxW*boxNum+'px;margin:0 auto';

		var boxArr=[]//用于存储每列的高度。
		for(var i=0;i<aBox.length;i++){
			aBox[i].style.position='absolute';
			var boxH=aBox[i].offsetHeight;
			if(i<boxNum){//6
				boxArr[i]=boxH;//把第一行中的所有高度存入boxArr中以便，后续图片排列；
				aBox[i].style.top=0;
				aBox[i].style.left=oBoxW*i+'px';
			}else{
				var minH=Math.min.apply(null,boxArr);//计算出最小的那个值；
				var minIndex=getMinIndex(boxArr,minH);
				aBox[i].style.top=minH+'px';
				aBox[i].style.left=minIndex*oBoxW+'px';//等于上一个最小值的left
				boxArr[minIndex]+=aBox[i].offsetHeight;//更新添加了块框后的列高
				
			}
		}
		var maxH=Math.max.apply(null,boxArr);
		oParent.style.height=maxH+'px';
		
	}
	function getMinIndex(arr,minH){
		for(var i in arr){
			if(arr[i]==minH){
				return i;
			}
		}

	}
	function getByClass(oParent,oClass){
		var arr=[];
		var obj=oParent.getElementsByTagName('*');
		for(var i=0;i<obj.length;i++){
			if(obj[i].className.indexOf(oClass) >-1 ){
			    arr.push(obj[i]);
			}
		}
		return arr;
	}	
	function scrollBotom(){//判断是否到底部了
	    var oParent=document.getElementById('main');
	    var aBox=getByClass(oParent,'box');
	    var lastBoxH=aBox[aBox.length-1].offsetTop+Math.floor(aBox[aBox.length-1].offsetHeight/2);
	    //获取到最后一个图片的offseTop+自身一半高，看是否小于滚动条的scroolTop；
	    var scrollTop=document.documentElement.scrollTop||document.body.scrollTop;//解决兼容性
	    var documentH=document.documentElement.clientHeight;//页面高度
	    return (lastBoxH<scrollTop+documentH)?true:false;//到达指定高度后 返回true
	}
	window.onload = function(){
		waterFall('main','box');//初始化。
	}