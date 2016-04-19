<?php 
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK IT ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006-2012 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: zhuzhaohui <smileszzh@163.com>
// +----------------------------------------------------------------------
// TypeEvent.class.php 2016-03-31
class OrderService{
/**
 * 推送订单 （to 顺丰）
 * orderid              订单号
 * j_company            寄件方公司名称
 * j_contact            寄件方联系人
 * j_telphone           寄件方联系电话
 * j_address            寄件地址
 * d_company            到件方公司名称
 * d_contact            到件方联系人
 * d_telphone           到件方联系电话
 * d_address            到件方地址
 * d_province           到件方省份
 * d_city               到件方城市
 * j_province           寄件方省份
 * j_city               寄件方城市
 * name                 商品名称
 * mailno               运单号
 * */

function xmlservice($orderid,$j_contact,$j_telphone,$j_address,$d_company,$d_contact,$d_telphone,$d_address) 
{
    //$d_deliverycode = $this->citycode($d_province,$d_city);<?xml version="1.0" encoding="UTF-8" 
	$body = '<Request service="OrderReverseService" lang="zh-CN"><Head>'.C('EXPRESS_CHECKHEADER')['SF'].'</Head><Body><Order orderid="'.$orderid.'" express_type="1"  j_contact="'.$j_contact.'" j_mobile="'.$j_telphone.'" j_address="'.$j_address.'"  d_company="'.$d_company.'" d_contact="'.$d_contact.'" d_mobile="'.$d_telphone.'" d_address="'.$d_address.'" parcel_quantity="1" pay_method="1" custid="'.C('MONTHLY_NUM').'" need_return_tracking_no="1" ><Cargo name="衣服" unit="件" /><AddedService name="INSURE" value="500" /></Order></Body></Request>';
     
     $newbody = $body.C('EXPRESS_CHECKWORD')['SF'];
	$md5 =  md5($newbody,true);  
	$verifyCode = base64_encode($md5);
	$url = C('EXPRESS_URL')['SF']; 
	$fields = array('xml'=>$body,'verifyCode'=>$verifyCode);
	$parambody =  http_build_query($fields, '', '&'); 
	$res = $this->post($url,$parambody); 
	return $res;
}



function xmlserviceback($orderid) 
{
    //$d_deliverycode = $this->citycode($d_province,$d_city);<?xml version="1.0" encoding="UTF-8" 
     $body = '<Request service="OrderRvsCancelService" lang="zh-CN"><Head>'.C('EXPRESS_CHECKHEADER')['SF'].'</Head><Body><Order orderid="'.$orderid.'"  ></Order></Body></Request>';
     
     $newbody = $body.C('EXPRESS_CHECKWORD')['SF'];
     $md5 =  md5($newbody,true);  print_r($newbody);die;
     $verifyCode = base64_encode($md5);
     $url = C('EXPRESS_URL')['SF']; 
     $fields = array('xml'=>$body,'verifyCode'=>$verifyCode);
     $parambody =  http_build_query($fields, '', '&'); 
     $res = $this->post($url,$parambody); 
     return $res;
}




/**
 * xml传送
 */
function post($url,$body) 
{ 
     $curlObj = curl_init();
     curl_setopt($curlObj, CURLOPT_URL, $url); // 设置访问的url
     curl_setopt($curlObj, CURLOPT_RETURNTRANSFER, 1); //curl_exec将结果返回,而不是执行
     curl_setopt($curlObj, CURLOPT_HTTPHEADER, array("Content-Type: application/x-www-form-urlencoded;charset=UTF-8"));
     curl_setopt($curlObj, CURLOPT_URL, $url);
     curl_setopt($curlObj, CURLOPT_SSL_VERIFYPEER, FALSE);
     curl_setopt($curlObj, CURLOPT_SSL_VERIFYHOST, FALSE);
     curl_setopt($curlObj, CURLOPT_SSLVERSION, CURL_SSLVERSION_TLSv1);
	
     curl_setopt($curlObj, CURLOPT_CUSTOMREQUEST, 'POST');      

     curl_setopt($curlObj, CURLOPT_POST, true);
     curl_setopt($curlObj, CURLOPT_POSTFIELDS, $body);       
     curl_setopt($curlObj, CURLOPT_ENCODING, 'gzip');

     $res = @curl_exec($curlObj);

     curl_close($curlObj);

     // if ($res === false) {
     //      $errno = curl_errno($curlObj);
     //      if ($errno == CURLE_OPERATION_TIMEOUTED) {
     //           $msg = "Request Timeout:   seconds exceeded";
     //      } else {
     //           $msg = curl_error($curlObj);
     //      }
     //      echo $msg;
     //      $e = new XN_TimeoutException($msg);           
     //      throw $e;
     // } 
	return $res;
}




/**
 *转换代码
 * 中文城市名称 与寄件方代码、到件方代码  转换
 * province     省份
 * city         城市
 * */
function citycode($province,$city){
  $expressname = array(	 
                    '北京'=>'010','平谷'=>'010','延庆'=>'010',
                    '广州'=>'020','增城'=>'020','从化'=>'020',
                    '上海'=>'021',
                    '天津'=>'022','静海'=>'022','蓟县'=>'022','宁河'=>'022',
                    '重庆'=>'023','渝中'=>'023','高新'=>'023','南岸'=>'023','万州'=>'023','壁山'=>'023','开县'=>'023','荣昌'=>'023','铜梁'=>'023','綦江'=>'023',
                    '沈阳'=>'024','抚顺'=>'024','锦州'=>'024',
                    '南京'=>'025','溧水县'=>'025','高淳县'=>'025',
                    '武汉'=>'027',
                    '成都'=>'028','双流'=>'028','都江堰'=>'028', '崇州'=>'028', '新津'=>'028','金堂'=>'028','大邑'=>'028','邛崃'=>'028','郫县'=>'028','彭州'=>'028','蒲江'=>'028','邛崃'=>'028',
                    '西安'=>'029',
                    '遵义'=>'052',
                    '安顺'=>'053',
                    '丽江'=>'088',
                    '邯郸'=>'310','成安县'=>'310','永年县'=>'310',
                    '石家庄'=>'311','鹿泉'=>'311','辛集'=>'311','晋州'=>'311','新乐'=>'311','正定'=>'311','无极'=>'311','藁城'=>'311','灵寿'=>'311','元氏'=>'311','赵县'=>'311',
                    '保定'=>'312','蠡县'=>'312','高阳'=>'312','雄县'=>'312','高碑县'=>'312','容城'=>'312','安新'=>'312','徐水'=>'312','定州'=>'312','满城'=>'312','定兴'=>'312','涿州'=>'312','安国'=>'312','清苑'=>'312',
                    '张家口'=>'313',
                    '承德'=>'314',
                    '唐山'=>'315','玉田县'=>'315','唐海'=>'315','迁西'=>'315',
                    '廊坊'=>'316','霸州'=>'316','香河县'=>'316','文安'=>'316','大城'=>'316','固安'=>'316','三河'=>'316','大厂'=>'316',
                    '沧州'=>'317','黄骅'=>'317','青县'=>'317','沧县'=>'317','南皮'=>'317','泊头'=>'317','任丘'=>'317',
                    '衡水'=>'318','安平县'=>'318','枣强'=>'318',
                    '邢台'=>'319','宁晋'=>'319','清河'=>'319','南宫'=>'319','新河'=>'319',
                    '秦皇岛'=>'335','昌黎'=>'335','抚宁'=>'335',
                    '太原'=>'351',
                    '晋中'=>'354',
                    '长治'=>'355',
                    '商丘'=>'370',
                    '郑州'=>'371','新郑'=>'371','荥阳'=>'371','新密'=>'371',
                    '安阳'=>'372','安阳县'=>'372',
                    '新乡'=>'373','卫辉'=>'373','辉县'=>'373',
                    '许昌'=>'374','长葛'=>'374','禹州'=>'374',
                    '平顶山'=>'375',
                    '信阳'=>'376',
                    '南阳'=>'377',
                    '开封'=>'378','尉氏'=>'378','中牟'=>'378',
                    '洛阳'=>'379','新安'=>'379','偃师'=>'379','宜阳'=>'379',
                    '焦作'=>'391',
                    '济源'=>'391E',
                    '鹤壁'=>'392',
                    '濮阳'=>'393',
                    '周口'=>'394',
                    '漯河'=>'395',
                    '驻马店'=>'396',
                    '三门峡'=>'398',
                    '铁岭'=>'410',
                    '大连'=>'411','普兰店'=>'411','瓦房店'=>'411','庄河'=>'411',
                    '鞍山'=>'412','海城'=>'412',
                    '本溪'=>'414',
                    '丹东'=>'415','东巷'=>'415',
                    '营口'=>'417','大石桥'=>'417',
                    '阜新'=>'418',
                    '辽阳'=>'419','灯塔'=>'419',
                    '朝阳'=>'421',
                    '盘锦'=>'427','大洼'=>'427','盘山'=>'427',
                    '葫芦岛'=>'429',
                    '兴城'=>'429',
                    '长春'=>'431',
                    '吉林'=>'432',
                    '延吉'=>'433',
                    '吉林省四平市'=>'434',
                    '哈尔滨'=>'451','双城'=>'451',
                    '齐齐哈尔'=>'452',
                    '大庆'=>'459',
                    '呼和浩特'=>'471','和林格尔县'=>'471',
                    '包头'=>'472',
                    '无锡'=>'510','江阴'=>'510','宜兴'=>'510',
                    '镇江'=>'511','杨中'=>'511', '句容'=>'511','丹阳'=>'511',
                    '苏州'=>'512','常昆'=>'512','吴江'=>'512','常熟'=>'512','太仓'=>'512','昆山'=>'512','张家港'=>'512',
                    '南通'=>'513','海门'=>'513','如东'=>'513','如皋'=>'513','海安'=>'513','启东'=>'513',
                    '杨州'=>'514','江都'=>'514','高邮'=>'514','仪征'=>'514','宝应'=>'514',
                    '盐城'=>'515','太丰'=>'515','东台'=>'515','建湖'=>'515','射阳'=>'515','阜宁'=>'515',
                    '徐州'=>'516','邳州'=>'516','新沂'=>'516',
                    '淮安'=>'517','涟水'=>'517','盯眙'=>'517','洪泽'=>'517','金湖'=>'517',
                    '连云港'=>'518','东海县'=>'518','赣榆县'=>'518',
                    '常州'=>'519','天宁'=>'519','武进'=>'519','龙城'=>'519','金坛'=>'519','溧阳'=>'519',
                    '泰州'=>'523','靖江'=>'523','泰兴'=>'523','姜堰'=>'523','兴化'=>'523',
                    '宿迁'=>'527','泗阳'=>'527','沭阳'=>'527',
                    '荷泽'=>'530',
                    '济南'=>'531','章丘'=>'531','济阳'=>'531',
                    '青岛'=>'532','即墨'=>'532','平度'=>'532','胶州'=>'532','胶南'=>'532','莱西'=>'532',
                    '淄博'=>'533','恒台'=>'533','高青'=>'533','邹平'=>'533','沂源'=>'533',
                    '德州'=>'534','宁津'=>'534','禹城'=>'534','齐河'=>'534','陵县'=>'534','夏津'=>'534','乐陵'=>'534','临邑'=>'534','平原'=>'534','武城'=>'534','高唐'=>'534',
                    '烟台'=>'535', '莱阳'=>'535', '海阳'=>'535', '莱州'=>'535', '蓬莱'=>'535', '龙口'=>'535', '栖霞'=>'535', '招远'=>'535',
                    '潍坊'=>'536','高密'=>'536','诸城'=>'536','昌邑'=>'536','寿光'=>'536','昌乐'=>'536','青州'=>'536','安丘'=>'536','临朐'=>'536',  
                    '济宁'=>'537', '邹城'=>'537', '嘉祥'=>'537', '兖州'=>'537', '曲阜'=>'537', 
                    '泰安'=>'538', '肥城'=>'538', 
                    '临沂'=>'539','沂水'=>'539','临沭'=>'539','蒙阴'=>'539', '沂南'=>'539', 
                    '滨州'=>'543', '博兴'=>'543', '沾化'=>'543', '无棣'=>'543', '阳信'=>'543', 
                    '东营'=>'546', '广饶'=>'546','垦利'=>'546',
                    '滁州'=>'550', '来安'=>'550','全椒'=>'550',
                    '合肥'=>'551','包河'=>'551','蜀山'=>'551','庐阳'=>'551','肥西'=>'551','长丰'=>'551','肥东'=>'551', 
                    '蚌埠'=>'552', '凤阳'=>'552','怀远'=>'552','固镇'=>'552',
                    '芜湖'=>'553', 
                    '淮南'=>'554', 
                    '马鞍山'=>'555', 
                    '安庆'=>'556', '桐城'=>'556', 
                    '宿州'=>'557', 
                    '阜阳'=>'558', 
                    '毫州'=>'558B', 
                    '黄山'=>'559','歙县'=>'559', '休宁'=>'559',  
                    '淮北市'=>'561', 
                    '铜陵'=>'562', 
                    '宣城'=>'563','广德'=>'563','宁国'=>'563', 
                    '六安'=>'564', 
                    '巢湖'=>'565', '半汤'=>'565', '无为县'=>'565', 
                    '池州'=>'566', 
                    '衢州'=>'570','江山'=>'570',  
                    '杭州'=>'571','富阳'=>'571','临安'=>'571','桐庐'=>'571', '建德'=>'571', '淳安'=>'571', 
                    '湖州'=>'572', '德清'=>'572','长兴'=>'572','安吉'=>'572',
                    '嘉兴'=>'573','嘉善'=>'573','桐乡'=>'573','海宁'=>'573','海盐'=>'573','平湖'=>'573', 
                    '宁波'=>'574', '鄞州'=>'574','海曙'=>'574','江北'=>'574','北仓'=>'574','江东'=>'574','下应'=>'574','奉化'=>'574','余姚'=>'574','慈溪'=>'574','宁海'=>'574','象山'=>'574',
                    '沼兴'=>'575', '越城'=>'575','袍江'=>'575','诸暨'=>'575','嵊州'=>'575','柯桥'=>'575','新昌县'=>'575','上虞'=>'575',
                    '台州'=>'576', '温岭'=>'576', '临海'=>'576', '玉环'=>'576', '仙居'=>'576', '天台'=>'576', '三门'=>'576', 
                    '温州'=>'577','乐清'=>'577','瑞安'=>'577','永嘉'=>'577','苍南'=>'577','平阳'=>'577','文成'=>'577','洞头'=>'577', 
                    '丽水'=>'578', '云和'=>'578', '青田'=>'578', '松阳'=>'578', '缙云'=>'578', '遂昌'=>'578', '龙泉'=>'578', 
                    '金华'=>'579', '义乌'=>'579','东阳市'=>'579','武义县'=>'579','兰溪'=>'579','浦江'=>'579','永康'=>'579','磐安'=>'579',
                    '舟山'=>'580', 
                    '福州福清长乐'=>'591', '闽侯'=>'591', '连江'=>'591', '闽清'=>'591', '罗源'=>'591', '永泰'=>'591', '平潭'=>'591', 
                    '夏门'=>'592','集美'=>'592', 
                    '宁德'=>'593','福安'=>'593','福鼎'=>'593','霞浦'=>'593','古田'=>'593','柘荣'=>'593','周宁'=>'593','屏南'=>'593','寿宁'=>'593',
                    '莆田'=>'594','仙游'=>'594',
                    '泉州'=>'595','晋江'=>'595','南安'=>'595','惠安'=>'595','石狮'=>'595','安溪'=>'595','永春'=>'595','德化'=>'595',
                    '漳州'=>'596','漳浦'=>'596','龙海'=>'596','长泰'=>'596','南靖'=>'596','石霄'=>'596','东山'=>'596','平和'=>'596','华安'=>'596','诏安'=>'596',
                    '龙岩'=>'597','漳平'=>'597','长汀'=>'597','永定'=>'597','上杭'=>'597','连城'=>'597',
                    '三明'=>'598','永安'=>'598','沙县'=>'598','龙溪'=>'598','明溪'=>'598','将乐'=>'598','大田'=>'598','宁化'=>'598','泰宁'=>'598',
                    '南平'=>'599','建瓯'=>'599','邵武'=>'599','建阳'=>'599','顺昌'=>'599','光泽'=>'599','武夷山'=>'599','浦城'=>'599','政和'=>'599','松溪'=>'599',
                    '威海'=>'631','荣城'=>'631','文登'=>'631','乳山'=>'631',
                    '枣庄'=>'632',
                    '日照'=>'633','五莲'=>'633',
                    '莱芜'=>'634','新泰'=>'634',
                    '聊城'=>'635','东阿'=>'635','临清'=>'635','茌平'=>'635','阳谷'=>'635',
                    '汕尾'=>'660','海丰'=>'660','陆丰'=>'660',
                    '阳江'=>'662','阳东'=>'662','阳西'=>'662','阳春'=>'662',
                    '揭阳'=>'663','揭东'=>'663','梅州丰顺'=>'663','普宁'=>'663','揭西'=>'663','惠来'=>'663',
                    '茂名'=>'668','高州'=>'668','吴川'=>'668','化州'=>'668','信宜'=>'668','电白'=>'668',
                    '西双版纳州'=>'691','景洪市'=>'691','勐海县'=>'691',
                    '鹰潭'=>'701','贵溪'=>'701','余江'=>'701',
                    '襄樊'=>'710','枣阳'=>'710',
                    '鄂州'=>'711','泽林镇'=>'711','葛店镇'=>'711',
                    '孝感'=>'712','毛陈镇'=>'712',
                    '汉川'=>'712B','新河镇'=>'712B','黄冈'=>'713','团风'=>'713',
                    '黄石'=>'714','大冶'=>'714',
                    '咸宁'=>'715A','715B'=>'赤壁',
                    '荆州'=>'716',
                    '宜昌'=>'717','枝江'=>'717','宜都'=>'717','当阳'=>'717',
                    '恩施'=>'718','十堰'=>'719',
                    '随州'=>'722','淅河镇'=>'722','厉山镇'=>'722',
                    '荆门'=>'724','钟祥'=>'724',
                    '潜江'=>'728','仙桃'=>'728','天门'=>'728',
                    '岳阳'=>'730','临湘'=>'730',
                    '长沙'=>'7311','浏阳'=>'7311','宁乡'=>'7311','望城'=>'7311','长沙县'=>'7311',
                    '湘潭'=>'7312','湘乡'=>'7312',
                    '株州'=>'7313','醴陵'=>'7313',
                    '衡阳'=>'734','耒阳'=>'734',
                    '郴州'=>'735','资兴'=>'735',
                    '常德'=>'736','桃源'=>'736',
                    '益阳'=>'737',
                    '娄底'=>'738A',
                    '邵阳'=>'739','邵东'=>'739','新邵'=>'739',
                    '吉首'=>'743',
                    '张家界'=>'744',
                    '怀化'=>'745',
                    '永州'=>'746',
                    '江门'=>'750','开平'=>'750','台山'=>'750','恩平'=>'750','鹤山'=>'750',
                    '韶关'=>'751','曲江'=>'751','乐昌'=>'751','乳源'=>'751','仁化'=>'751','南雄'=>'751','始兴'=>'751','翁源'=>'751',
                    '惠州'=>'752', '博罗'=>'752', '惠东'=>'752', '海丰县鹅埠镇圆墩镇'=>'752', '龙门'=>'752',
                     '梅州'=>'753', '梅县'=>'753', '兴宁'=>'753', '五华'=>'753', '蕉岭'=>'753', '平远'=>'753', '大浦'=>'753',
                    '汕头'=>'754','澄海'=>'754','潮阳'=>'754',
                    '深圳西丽分部'=>'755AP',
                    '珠海'=>'756',
                    '佛山'=>'757','南海'=>'757','高明'=>'757','三水'=>'757','顺德'=>'757',
                    '肇庆'=>'758','高要'=>'758','四会'=>'758','广宁'=>'758','德庆'=>'758',
                    '湛江'=>'759','遂溪'=>'759','廉江'=>'759','雷州'=>'759','徐闻'=>'759',
                    '中山'=>'760','小榄'=>'760','石岐'=>'760','三乡'=>'760','三角'=>'760','沙溪'=>'760',
                    '河源'=>'762','东源'=>'762','紫金'=>'762','龙川'=>'762','连平'=>'762','和平'=>'762',
                    '清远'=>'763','清新'=>'763','英德'=>'763','佛冈'=>'763','连州'=>'763','阳山'=>'763','连南'=>'763',
                    '云浮'=>'766','新兴'=>'766','罗定'=>'766','云安'=>'766',
                    '潮州'=>'768','潮安'=>'768','饶平'=>'768',
                    '东莞'=>'769',
                    '防城港市'=>'770A','东兴'=>'770A',
                    '南宁'=>'771','武鸣'=>'771','横县'=>'771','崇左市'=>'771','凭祥'=>'771',
                    '柳州'=>'772','柳江'=>'772','鹿寨'=>'772',
                    '桂林'=>'773','灵川'=>'773','临桂'=>'773','荔浦'=>'773','阳朔'=>'773',
                    '梧州'=>'774','苍梧'=>'774','岑溪'=>'774',
                    '玉林'=>'775','北流'=>'775',
                    '贵港市'=>'775B',
                    '钦州'=>'777A',
                    '北海'=>'779',
                    '新余'=>'790A','分宜'=>'790A',
                    '南昌'=>'791','新建'=>'791','进贤'=>'791',
                    '九江'=>'792','德安'=>'792','瑞昌'=>'792','湖口'=>'792','都昌'=>'792',
                    '上饶'=>'793','广丰'=>'793','玉山'=>'793','戈阳'=>'793','横峰'=>'793','铅山'=>'793',
                    '抚州'=>'794','上顿渡镇'=>'794','东乡县'=>'794',
                    '宜春'=>'795','万载'=>'795','高安'=>'795','樟树'=>'795','丰城'=>'795',
                    '吉安'=>'796',
                    '赣州'=>'797','赣县'=>'797','南康市'=>'797','信丰'=>'797','龙南'=>'797',
                    '景德镇'=>'798','浮梁'=>'798','乐平'=>'798',
                    '萍乡'=>'799','泸溪'=>'799',
                    '攀枝花'=>'812',
                    '自贡'=>'813',
                    '绵阳'=>'816','江油'=>'816','三台'=>'816','安县'=>'816',
                    '南充'=>'817','阆中市'=>'817','南部县'=>'817',
                    '达州'=>'818','达县'=>'818',
                    '遂宁'=>'825',
                    '广安'=>'826A',
                    '巴中'=>'827A',
                    '泸州'=>'830','泸县'=>'830',
                    '宜宾'=>'831',
                    '内江'=>'832','隆昌'=>'832','资阳'=>'832',
                    '乐山'=>'833',
                    '眉山'=>'833B',
                    '雅安'=>'835',
                    '西昌'=>'834A',
                    '德阳'=>'838','广汉'=>'838','绵竹'=>'838','什邡'=>'838',
                    '广元'=>'839',
                    '贵阳'=>'851','龙洞堡'=>'851',
                    '香港'=>'852',
                    '澳门'=>'853',
                    '都匀'=>'854',
                    '凯里市'=>'855A',
                    '毕节市'=>'857A',
                    '六盘水'=>'858',
                    '昆明'=>'871','官渡古镇'=>'871','小板桥镇'=>'871','呈贡县'=>'871',
                    '大理'=>'872',
                    '红河州蒙自市'=>'873A',
                    '个旧市'=>'873B',
                    '曲靖'=>'874',
                    '云南保山'=>'875',
                    '云南文山州'=>'876',
                    '玉溪'=>'877',
                    '楚雄'=>'878A',
                    '普洱市'=>'879',
                    '台湾'=>'886',
                    '拉萨市'=>'891',
                    '海口'=>'898',
                    '三亚'=>'8981',
                    '咸阳'=>'910',
                    '渭南'=>'913',
                    '汉中'=>'916',
                    '宝鸡'=>'917','岐山'=>'917',
                    '兰州'=>'931',
                    '银川'=>'951',
                    '西宁'=>'971',
                    '乌鲁木齐'=>'991'
  );
    foreach($expressname as $keys => $values){
        if(strstr($province,$keys)){
            //echo $keys."=>".$values;
            $code = $values;
            break;
        }
        elseif(strstr($city,$keys)){
            //echo $keys."=>".$values;
            $code = $values;
            break;
        }   
   };
     return $code;  
}


}
