/**
 * 会员头像修改
 * @作者 qinggan <admin@phpok.com>
 * @版权 2008-2018 深圳市锟铻科技有限公司
 * @网站 http://www.phpok.com
 * @版本 5.x
 * @授权 http://www.phpok.com/lgpl.html PHPOK开源授权协议：GNU Lesser General Public License
 * @日期 2018年10月26日
**/

var chooseGallery;
var chooseCamera;
var cropImage;
var imgData;
var clipContent;
var clipAction;
var showContent;
var showImg;
var targetImg;
var targetImgCamera;

initPage();

function initPage() {
	initParams();
	initListeners();
	initImgClip();
}

function initParams() {
	targetImg = document.querySelector('#targetImg');
	targetImgCamera = document.querySelector('#targetImgCamera');
	chooseGallery = document.querySelector('.choose-gallery');
	chooseCamera = document.querySelector('.choose-camera');
	clipContent = document.querySelector('.clip-content');
	clipAction = document.querySelector('.clip-action');
	showContent = document.querySelector('.show-content');
	showImg = document.querySelector('.show-img');
}

function initImgClip() {
	new FileInput({
		container: '#targetImg',
		isMulti: false,
		type: 'Image_Camera',
		success: function(b64, file, detail) {
			loadImg(b64);
		},
		error: function(error) {
			console.error(error);
		}
	});
	new FileInput({
		container: '#targetImgCamera',
		isMulti: false,
		type: 'Camera',
		success: function(b64, file, detail) {
			loadImg(b64);
		},
		error: function(error) {
			console.error(error);
		}
	});
}

function loadImg(b64) {
	changeImgClipShow(true);

	var img = new Image();
	img.src = b64;

	img.onload = function() {
		EXIF.getData(img, function() {
			var orientation = EXIF.getTag(this, 'Orientation');
			
			cropImage && cropImage.destroy();
			cropImage = new ImageClip({
				container: '.img-clip',
				img,
				// 0代表按下才显示，1恒显示，-1不显示
				sizeTipsStyle: 0,
				// 为1一般是屏幕像素x2这个宽高
				// 最终的大小为：屏幕像素*屏幕像素比（手机中一般为2）*compressScaleRatio
				compressScaleRatio: 1.1,
				// iphone中是否继续放大：x*iphoneFixedRatio
				// 最好compressScaleRatio*iphoneFixedRatio不要超过2
				iphoneFixedRatio: 1.8,
				// 减去顶部间距，底部bar,以及显示间距
				maxCssHeight: window.innerHeight - 100 - 50 - 20,
				// 放大镜捕获的图像半径
				captureRadius: 30,
				// 是否采用原图像素（不会压缩）
				isUseOriginSize: false,
				// 增加最大宽度，增加后最大不会超过这个宽度
				maxWidth: 500,
				// 是否固定框高，优先级最大，设置后其余所有系数都无用直接使用这个固定的宽，高度自适应
				forceWidth: 0,
				// 同上，但是一般不建议设置，因为很可能会改变宽高比导致拉升，特殊场景下使用
				forceHeight: 0,
				// 压缩质量
				quality: 0.92,
				mime: 'image/jpeg',
			});

			// 6代表图片需要顺时针修复（默认逆时针处理了，所以需要顺过来修复）
			switch (orientation) {
				case 6:
					cropImage.rotate(true);
					break;
				default:
					break;
			}

		});
	};
}

function resizeShowImg(b64) {
	var img = new Image();

	img.src = b64;
	img.onload = showImgOnload;
}

function showImgOnload() {
	// 必须用一个新的图片加载，否则如果只用showImg的话永远都是第1张
	// margin的话由于有样式，所以自动控制了
	var width = this.width;
	var height = this.height;
	var wPerH = width / height;
	var MAX_WIDTH = Math.min(window.innerWidth, width);
	var MAX_HEIGHT = Math.min(window.innerHeight - 50 - 100, height);
	var legalWidth = MAX_WIDTH;
	var legalHeight = legalWidth / wPerH;

	if (MAX_WIDTH && legalWidth > MAX_WIDTH) {
		legalWidth = MAX_WIDTH;
		legalHeight = legalWidth / wPerH;
	}
	if (MAX_HEIGHT && legalHeight > MAX_HEIGHT) {
		legalHeight = MAX_HEIGHT;
		legalWidth = legalHeight * wPerH;
	}
	showImg.style.marginTop = '10%';
	showImg.style.width = legalWidth + 'px';
	showImg.style.height = legalHeight + 'px';
}

function changeImgClipShow(isClip) {
	if (isClip) {
		chooseGallery.classList.add('hidden');
		chooseCamera.classList.add('hidden');
		clipAction.classList.remove('hidden');
	} else {
		chooseGallery.classList.remove('hidden');
		chooseCamera.classList.remove('hidden');
		clipAction.classList.add('hidden');
		targetImg.value = '';
		targetImgCamera.value = '';
	}
}

function initListeners() {
	document.querySelector('#btn-reload').addEventListener('click', function() {
		cropImage && cropImage.destroy();
		changeImgClipShow(false);
	});
	document.querySelector('#btn-back').addEventListener('click', function() {
		changeContent(false);
	});
	document.querySelector('#btn-save').addEventListener('click', function() {
		var obj = {};
		obj.data = imgData;
		var tipobj = $.dialog.tips('正在上传中，请稍候…',100).lock();
		$.phpok.json(api_url("usercp","avatar","type=base64"),function(rs){
			tipobj.close();
			if(rs.status){
				$.dialog.alert('头像更新成功',function(){
					$.phpok.go(get_url('usercp'));
				},'success');
				return true;
			}
			$.dialog.alert(rs.info);
			return false;
		},obj);
	});
	document.querySelector('#btn-detail').addEventListener('click', function() {
		showImgDataLen(imgData);
	});

	document.querySelector('#btn-maxrect').addEventListener('click', function() {
		if (!cropImage) {
			$.dialog.alert('请选择图片');
			return;
		}
		cropImage.resetClipRect();
	});

	document.querySelector('#btn-rotate-anticlockwise').addEventListener('click', function() {
		if (!cropImage) {
			$.dialog.alert('请选择图片');
			return;
		}
		cropImage.rotate(false);
	});

	document.querySelector('#btn-rotate-clockwise').addEventListener('click', function() {
		if (!cropImage) {
			$.dialog.alert('请选择图片');
			return;
		}
		cropImage.rotate(true);
	});

	document.querySelector('#btn-verify').addEventListener('click', function() {
		if (!cropImage) {
			$.dialog.alert('请选择图片');
			return;
		}
		$.dialog.confirm('是否裁剪图片并处理？',function(){
			cropImage.clip(false);
			imgData = cropImage.getClipImgData();
			recognizeImg(function() {
				changeContent(true);
			}, function(error) {
				$.dialog.alert(JSON.stringify(error));
			});
		});
	});
}

function showImgDataLen(imgData) {
	var len = imgData.length;
	var sizeStr = len + 'B';

	if (len > 1024 * 1024) {
		sizeStr = (Math.round(len / (1024 * 1024))).toString() + 'MB';
	} else if (len > 1024) {
		sizeStr = (Math.round(len / 1024)).toString() + 'KB';
	}
	$.dialog.alert('处理后文件大小：'+sizeStr);
}

function changeContent(isShowContent) {
	if (isShowContent) {
		showContent.classList.remove('hidden');
		clipContent.classList.add('hidden');

		resizeShowImg(imgData);
		showImg.src = imgData;

	} else {
		showContent.classList.add('hidden');
		clipContent.classList.remove('hidden');
	}
}

function b64ToBlob(urlData) {
	var arr = urlData.split(',');
	var mime = arr[0].match(/:(.*?);/)[1] || 'image/png';
	// 去掉url的头，并转化为byte
	var bytes = window.atob(arr[1]);

	// 处理异常,将ascii码小于0的转换为大于0
	var ab = new ArrayBuffer(bytes.length);
	// 生成视图（直接针对内存）：8位无符号整数，长度1个字节
	var ia = new Uint8Array(ab);
	for (var i = 0; i < bytes.length; i++) {
		ia[i] = bytes.charCodeAt(i);
	}

	return new Blob([ab], {
		type: mime
	});
}

function downloadFile(content) {
	// Convert image to 'octet-stream' (Just a download, really)
	var imageObj = content.replace("image/jpeg", "image/octet-stream");
	window.location.href = imageObj;
}

function recognizeImg(success, error) {
	// 里面正常有：裁边，摆正，梯形矫正，锐化等算法操作
	success();
}

function upload(success, error) {
	success();
}
$(document).ready(function(){
	$("body").css('background','#0e90d2');
});
