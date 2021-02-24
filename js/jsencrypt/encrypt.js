/**
 * 加密操作，需要优先引入两个JS文件
 * 		文件1： jsencrypt.js 或 jsencrypt.min.js
 *		文件2：serialize.js
 *		如果不引入这两个文件，加密会失败的，服务端解密也会失败
 *		注意，这里用于通迅，主要就是前端用公钥加密，后端用私钥解密，所以前端这里不编写解密操作
 * @作者 苏相锟 <admin@phpok.com>
 * @主页 https://www.phpok.com
 * @版本 5.x
 * @授权 GNU Lesser General Public License  https://www.phpok.com/lgpl.html
 * @时间 2020年12月22日
**/

/**
 * 加密操作
 * @参数 data 要加密的数据，支持字符串，数组及对象（在这里系统会把对象也当成数组处理）
 * @参数 public_key 公钥证书代码，如果含用 -----public***----- 等信息会自动去除，会去除换行信息
**/
function encrypt(data,public_key)
{
	if(!data || data == 'undefined'){
		return false;
	}
	if(!public_key || public_key == 'undefined'){
		return false;
	}
	public_key = public_key.replace(/\-[\-]+(.*)\-\-/g,"");
	public_key = public_key.replace(/\n/g,"");
	var str = serialize(data);
	var strArr = new Array();
	var n = 117;
	var obj = new JSEncrypt();
	obj.setPublicKey(public_key); // 设置公钥
	for (var i = 0, l = str.length; i < l/n; i++) {
		var a = str.slice(n*i, n*(i+1));
		var t = obj.encrypt(a);
		strArr.push(t);
	}
	return strArr.join('');
}