<?php

use Carbon\Carbon;
use Illuminate\Database\Seeder;

class LocationNetworkTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $isp = [
            'ct' => 'China Telecom',
            'cu' => 'China Unicom',
            'cm' => 'China Mobile',
        ];

        $freeSetting = [
            [
                'location' => 'All',
                'isp' => 'All',
                'network_id' => 1, // 國內
                'mapping_value' => NULL,
            ], [
                'continent_id' => 2,
                'country_id' => 2,
                'location' => 'All',
                'isp' => 'All',
                'network_id' => 2, // 國外
                'mapping_value' => NULL,
            ], [
                'location' => 'All',
                'isp' => $isp['ct'],
                'network_id' => 3, // 电信
                'mapping_value' => 'ct',
            ], [
                'location' => 'All',
                'isp' => $isp['cu'],
                'network_id' => 4, // 联通
                'mapping_value' => 'cu',
            ], [
                'location' => 'All',
                'isp' => $isp['cm'],
                'network_id' => 6, // 移动
                'mapping_value' => 'cm',
            ],
        ];

        $enterprise = [
            [
                'location' => 'Anhui',
                'isp' => $isp['ct'],
                'network_id' => 31, // 安徽电信
                'mapping_value' => 'Anhui Telecom',
            ], [
                'location' => 'Anhui',
                'isp' => $isp['cu'],
                'network_id' => 32, // 安徽联通
                'mapping_value' => 'Anhui Unicom',
            ], [
                'location' => 'Beijing',
                'isp' => $isp['ct'],
                'network_id' => 33, // 北京电信
                'mapping_value' => 'Beijing Telecom',
            ], [
                'location' => 'Beijing',
                'isp' => $isp['cu'],
                'network_id' => 34, // 北京联通
                'mapping_value' => 'Beijing Unicom',
            ], [
                'location' => 'Chongqing',
                'isp' => $isp['ct'],
                'network_id' => 35, // 重庆电信
                'mapping_value' => 'Chongqing Telecom',
            ], [
                'location' => 'Chongqing',
                'isp' => $isp['cu'],
                'network_id' => 36, // 重庆联通
                'mapping_value' => 'Chongqing Unicom',
            ], [
                'location' => 'Fujian',
                'isp' => $isp['ct'],
                'network_id' => 37, // 福建电信
                'mapping_value' => 'Fujian Telecom',
            ], [
                'location' => 'Fujian',
                'isp' => $isp['cu'],
                'network_id' => 38, // 福建联通
                'mapping_value' => 'Fujian Unicom',
            ], [
                'location' => 'Gansu',
                'isp' => $isp['ct'],
                'network_id' => 39, // 甘肃电信
                'mapping_value' => 'Gansu Telecom',
            ], [
                'location' => 'Gansu',
                'isp' => $isp['cu'],
                'network_id' => 40, // 甘肃联通
                'mapping_value' => 'Gansu Unicom',
            ], [
                'location' => 'Guangdong',
                'isp' => $isp['ct'],
                'network_id' => 41, // 广东电信
                'mapping_value' => 'Guangdong Telecom',
            ], [
                'location' => 'Guangdong',
                'isp' => $isp['cu'],
                'network_id' => 42, // 广东联通
                'mapping_value' => 'Guangdong Unicom',
            ], [
                'location' => 'Guangxi',
                'isp' => $isp['ct'],
                'network_id' => 43, // 广西电信
                'mapping_value' => 'Guangxi Telecom',
            ], [
                'location' => 'Guangxi',
                'isp' => $isp['cu'],
                'network_id' => 44, // 广西联通
                'mapping_value' => 'Guangxi Unicom',
            ], [
                'location' => 'Guizhou',
                'isp' => $isp['ct'],
                'network_id' => 45, // 贵州电信
                'mapping_value' => 'Guizhou Telecom',
            ], [
                'location' => 'Guizhou',
                'isp' => $isp['cu'],
                'network_id' => 46, // 贵州联通
                'mapping_value' => 'Guizhou Unicom',
            ], [
                'location' => 'Hainan',
                'isp' => $isp['ct'],
                'network_id' => 47, // 海南电信
                'mapping_value' => 'Hainan Telecom',
            ], [
                'location' => 'Hainan',
                'isp' => $isp['cu'],
                'network_id' => 48, // 海南联通
                'mapping_value' => 'Hainan Unicom',
            ], [
                'location' => 'Hebei',
                'isp' => $isp['ct'],
                'network_id' => 49, // 河北电信
                'mapping_value' => 'Hebei Telecom',
            ], [
                'location' => 'Hebei',
                'isp' => $isp['cu'],
                'network_id' => 50, // 河北联通
                'mapping_value' => 'Hebei Unicom',
            ], [
                'location' => 'Henan',
                'isp' => $isp['ct'],
                'network_id' => 51, // 河南电信
                'mapping_value' => 'Henan Telecom',
            ], [
                'location' => 'Henan',
                'isp' => $isp['cu'],
                'network_id' => 52, // 河北联通
                'mapping_value' => 'Henan Unicom',
            ], [
                'location' => 'Heilongjiang',
                'isp' => $isp['ct'],
                'network_id' => 53, // 黑龙江电信
                'mapping_value' => 'Heilongjiang Telecom',
            ], [
                'location' => 'Heilongjiang',
                'isp' => $isp['cu'],
                'network_id' => 54, // 黑龙江联通
                'mapping_value' => 'Heilongjiang Unicom',
            ], [
                'location' => 'Hubei',
                'isp' => $isp['ct'],
                'network_id' => 55, // 湖北电信
                'mapping_value' => 'Hubei Telecom',
            ], [
                'location' => 'Hubei',
                'isp' => $isp['cu'],
                'network_id' => 56, // 湖北联通
                'mapping_value' => 'Hubei Unicom',
            ], [
                'location' => 'Hunan',
                'isp' => $isp['ct'],
                'network_id' => 57, // 湖南电信
                'mapping_value' => 'Hunan Telecom',
            ], [
                'location' => 'Hunan',
                'isp' => $isp['cu'],
                'network_id' => 58, // 湖南联通
                'mapping_value' => 'Hunan Unicom',
            ], [
                'location' => 'Jilin',
                'isp' => $isp['ct'],
                'network_id' => 59, // 吉林电信
                'mapping_value' => 'Jilin Telecom',
            ], [
                'location' => 'Jilin',
                'isp' => $isp['cu'],
                'network_id' => 60, // 吉林联通
                'mapping_value' => 'Jilin Unicom',
            ], [
                'location' => 'Jiangsu',
                'isp' => $isp['ct'],
                'network_id' => 61, // 江苏电信
                'mapping_value' => 'Jiangsu Telecom',
            ], [
                'location' => 'Jiangsu',
                'isp' => $isp['cu'],
                'network_id' => 62, // 江苏联通
                'mapping_value' => 'Jiangsu Unicom',
            ], [
                'location' => 'Jiangxi',
                'isp' => $isp['ct'],
                'network_id' => 63, // 江西电信
                'mapping_value' => 'Jiangxi Telecom',
            ], [
                'location' => 'Jiangxi',
                'isp' => $isp['cu'],
                'network_id' => 64, // 江西联通
                'mapping_value' => 'Jiangxi Unicom',
            ], [
                'location' => 'Liaoning',
                'isp' => $isp['ct'],
                'network_id' => 65, // 辽宁电信
                'mapping_value' => 'Liaoning Telecom',
            ], [
                'location' => 'Liaoning',
                'isp' => $isp['cu'],
                'network_id' => 66, // 辽宁联通
                'mapping_value' => 'Liaoning Unicom',
            ], [
                'location' => 'Inner Mongolia',
                'isp' => $isp['ct'],
                'network_id' => 67, // 内蒙电信
                'mapping_value' => 'Inner Mongolia Telecom',
            ], [
                'location' => 'Inner Mongolia',
                'isp' => $isp['cu'],
                'network_id' => 68, // 内蒙联通
                'mapping_value' => 'Inner Mongolia Unicom',
            ], [
                'location' => 'Ningxia',
                'isp' => $isp['ct'],
                'network_id' => 69, // 宁夏电信
                'mapping_value' => 'Ningxia Telecom',
            ], [
                'location' => 'Ningxia',
                'isp' => $isp['cu'],
                'network_id' => 70, // 宁夏联通
                'mapping_value' => 'Ningxia Unicom',
            ], [
                'location' => 'Qinghai',
                'isp' => $isp['ct'],
                'network_id' => 71, // 青海电信
                'mapping_value' => 'Qinghai Telecom',
            ], [
                'location' => 'Qinghai',
                'isp' => $isp['cu'],
                'network_id' => 72, // 青海联通
                'mapping_value' => 'Qinghai Unicom',
            ], [
                'location' => 'Shandong',
                'isp' => $isp['ct'],
                'network_id' => 73, // 山东电信
                'mapping_value' => 'Shandong Telecom',
            ], [
                'location' => 'Shandong',
                'isp' => $isp['cu'],
                'network_id' => 74, // 山东联通
                'mapping_value' => 'Shandong Unicom',
            ], [
                'location' => 'Shanxi',
                'isp' => $isp['ct'],
                'network_id' => 75, // 山西电信
                'mapping_value' => 'Shanxi Telecom',
            ], [
                'location' => 'Shanxi',
                'isp' => $isp['cu'],
                'network_id' => 76, // 山西电信
                'mapping_value' => 'Shanxi Unicom',
            ], [
                'location' => 'Shaanxi',
                'isp' => $isp['ct'],
                'network_id' => 77, // 陕西电信
                'mapping_value' => 'Shaanxi Telecom',
            ], [
                'location' => 'Shaanxi',
                'isp' => $isp['cu'],
                'network_id' => 78, // 陕西联通
                'mapping_value' => 'Shaanxi Unicom',
            ], [
                'location' => 'Shanghai',
                'isp' => $isp['ct'],
                'network_id' => 79, // 上海电信
                'mapping_value' => 'Shanghai Telecom',
            ], [
                'location' => 'Shanghai',
                'isp' => $isp['cu'],
                'network_id' => 80, // 上海联通
                'mapping_value' => 'Shanghai Unicom',
            ], [
                'location' => 'Sichuan',
                'isp' => $isp['ct'],
                'network_id' => 81, // 四川电信
                'mapping_value' => 'Sichuan Telecom',
            ], [
                'location' => 'Sichuan',
                'isp' => $isp['cu'],
                'network_id' => 82, // 四川联通
                'mapping_value' => 'Sichuan Unicom',
            ], [
                'location' => 'Tianjin',
                'isp' => $isp['ct'],
                'network_id' => 83, // 天津电信
                'mapping_value' => 'Tianjin Telecom',
            ], [
                'location' => 'Tianjin',
                'isp' => $isp['cu'],
                'network_id' => 84, // 天津联通
                'mapping_value' => 'Tianjin Unicom',
            ], [
                'location' => 'Tibet',
                'isp' => $isp['ct'],
                'network_id' => 85, // 西藏电信
                'mapping_value' => 'Tibet Telecom',
            ], [
                'location' => 'Tibet',
                'isp' => $isp['cu'],
                'network_id' => 86, // 西藏联通
                'mapping_value' => 'Tibet Unicom',
            ], [
                'location' => 'Xinjiang',
                'isp' => $isp['ct'],
                'network_id' => 87, // 新疆电信
                'mapping_value' => 'Xinjiang Telecom',
            ], [
                'location' => 'Xinjiang',
                'isp' => $isp['cu'],
                'network_id' => 88, // 新疆联通
                'mapping_value' => 'Xinjiang Unicom',
            ], [
                'location' => 'Yunnan',
                'isp' => $isp['ct'],
                'network_id' => 89, // 云南电信
                'mapping_value' => 'Yunnan Telecom',
            ], [
                'location' => 'Yunnan',
                'isp' => $isp['cu'],
                'network_id' => 90, // 云南联通
                'mapping_value' => 'Yunnan Unicom',
            ], [
                'location' => 'Zhejiang',
                'isp' => $isp['ct'],
                'network_id' => 91, // 浙江电信
                'mapping_value' => 'Zhejiang Telecom',
            ], [
                'location' => 'Zhejiang',
                'isp' => $isp['cu'],
                'network_id' => 92, // 浙江联通
                'mapping_value' => 'Zhejiang Unicom',
            ], [
                'location' => 'Hong Kong',
                'isp' => 'all',
                'network_id' => 93, // 香港
                'mapping_value' => 'Hong Kong',
            ], [
                'location' => 'Macao',
                'isp' => 'all',
                'network_id' => 94, // 澳门
                'mapping_value' => 'Macao',
            ], [
                'country_id' => 3,
                'location' => 'Taiwan',
                'isp' => 'all',
                'network_id' => 95, // 台湾
                'mapping_value' => 'Taiwan',
            ], [
                'location' => 'Beijing',
                'isp' => $isp['cm'],
                'network_id' => 101, // 北京移动
                'mapping_value' => 'Beijing Mobile',
            ], [
                'location' => 'Guangdong',
                'isp' => $isp['cm'],
                'network_id' => 102, // 广东移动
                'mapping_value' => 'Guangdong Mobile',
            ], [
                'location' => 'Zhejiang',
                'isp' => $isp['cm'],
                'network_id' => 103, // 浙江移动
                'mapping_value' => 'Zhejiang Mobile',
            ], [
                'location' => 'Shandong',
                'isp' => $isp['cm'],
                'network_id' => 104, // 山东移动
                'mapping_value' => 'Shandong Mobile',
            ], [
                'location' => 'Jiangsu',
                'isp' => $isp['cm'],
                'network_id' => 105, // 江苏移动
                'mapping_value' => 'Jiangsu Mobile',
            ], [
                'location' => 'Shanghai',
                'isp' => $isp['cm'],
                'network_id' => 106, // 上海移动
                'mapping_value' => 'Shanghai Mobile',
            ], [
                'location' => 'Sichuan',
                'isp' => $isp['cm'],
                'network_id' => 107, // 四川移动
                'mapping_value' => 'Sichuan Mobile',
            ], [
                'location' => 'Liaoning',
                'isp' => $isp['cm'],
                'network_id' => 108, // 辽宁移动
                'mapping_value' => 'Liaoning Mobile',
            ], [
                'location' => 'Hebei',
                'isp' => $isp['cm'],
                'network_id' => 109, // 河北移动
                'mapping_value' => 'Hebei Mobile',
            ], [
                'location' => 'Henan',
                'isp' => $isp['cm'],
                'network_id' => 110, // 河南移动
                'mapping_value' => 'Henan Mobile',
            ], [
                'location' => 'Hubei',
                'isp' => $isp['cm'],
                'network_id' => 111, // 湖北移动
                'mapping_value' => 'Hubei Mobile',
            ], [
                'location' => 'Hunan',
                'isp' => $isp['cm'],
                'network_id' => 112, // 湖南移动
                'mapping_value' => 'Hunan Mobile',
            ], [
                'location' => 'Fujian',
                'isp' => $isp['cm'],
                'network_id' => 113, // 福建移动
                'mapping_value' => 'Fujian Mobile',
            ], [
                'location' => 'Chongqing',
                'isp' => $isp['cm'],
                'network_id' => 114, // 重庆移动
                'mapping_value' => 'Chongqing Mobile',
            ], [
                'location' => 'Jiangxi',
                'isp' => $isp['cm'],
                'network_id' => 115, // 江西移动
                'mapping_value' => 'Jiangxi Mobile',
            ], [
                'location' => 'Anhui',
                'isp' => $isp['cm'],
                'network_id' => 116, // 安徽移动
                'mapping_value' => 'Anhui Mobile',
            ], [
                'location' => 'Shanxi',
                'isp' => $isp['cm'],
                'network_id' => 117, // 山西移动
                'mapping_value' => 'Shanxi Mobile',
            ], [
                'location' => 'Shaanxi',
                'isp' => $isp['cm'],
                'network_id' => 118, // 陕西移动
                'mapping_value' => 'Shaanxi Mobile',
            ], [
                'location' => 'Heilongjiang',
                'isp' => $isp['cm'],
                'network_id' => 119, // 黑龙江移动
                'mapping_value' => 'Heilongjiang Mobile',
            ], [
                'location' => 'Jilin',
                'isp' => $isp['cm'],
                'network_id' => 120, // 吉林移动
                'mapping_value' => 'Jilin Mobile',
            ], [
                'location' => 'Guangxi',
                'isp' => $isp['cm'],
                'network_id' => 121, // 广西移动
                'mapping_value' => 'Guangxi Mobile',
            ], [
                'location' => 'Tianjin',
                'isp' => $isp['cm'],
                'network_id' => 122, // 天津移动
                'mapping_value' => 'Tianjin Mobile',
            ], [
                'location' => 'Yunnan',
                'isp' => $isp['cm'],
                'network_id' => 123, // 云南移动
                'mapping_value' => 'Yunnan Mobile',
            ], [
                'location' => 'Inner Mongolia',
                'isp' => $isp['cm'],
                'network_id' => 124, // 内蒙移动
                'mapping_value' => 'Inner Mongolia Mobile',
            ], [
                'location' => 'Xinjiang',
                'isp' => $isp['cm'],
                'network_id' => 125, // 新疆移动
                'mapping_value' => 'Xinjiang Mobile',
            ], [
                'location' => 'Guizhou',
                'isp' => $isp['cm'],
                'network_id' => 126, // 贵州移动
                'mapping_value' => 'Guizhou Mobile',
            ], [
                'location' => 'Gansu',
                'isp' => $isp['cm'],
                'network_id' => 127, // 甘肃移动
                'mapping_value' => 'Gansu Mobile',
            ], [
                'location' => 'Hainan',
                'isp' => $isp['cm'],
                'network_id' => 128, // 海南移动
                'mapping_value' => 'Hainan Mobile',
            ], [
                'location' => 'Ningxia',
                'isp' => $isp['cm'],
                'network_id' => 129, // 宁夏移动
                'mapping_value' => 'Ningxia Mobile',
            ], [
                'location' => 'Qinghai',
                'isp' => $isp['cm'],
                'network_id' => 130, // 青海移动
                'mapping_value' => 'Qinghai Mobile',
            ], [
                'location' => 'Tibet',
                'isp' => $isp['cm'],
                'network_id' => 131, // 西藏移动
                'mapping_value' => 'Tibet Mobile',
            ]
        ];

        $nowTime = Carbon::now();
        foreach (array_merge($freeSetting, $enterprise) as $key => $value) {
            $value['created_at'] = $nowTime;
            $value['updated_at'] = $nowTime;
            $value['continent_id'] = $value['continent_id'] ?? 3;
            $value['country_id'] = $value['country_id'] ?? 1;

            DB::table('location_networks')->insert($value);
        }
    }
}
