/**
 * @Author: Kongho
 * @Date:   2017-01-18 14:20:34
 * @Email:  kongho@3ncto.com
 * @Filename: api.js
* @Last modified by:   Kongho
* @Last modified time: 2017-01-22 22:13:08
 * @Copyright: 3NCTO Co., Ltd.
 */



app.constant('apiConfig', {
    // 返回码
    errorCode: {
        success: '1'
    },

    // 接口列表
    // ==操作账户==
    apiAccount: {
        login: 'http://followbee.3ncto.com.cn/api/index.php/Home/index'
    },

    // ==产品类型==
    apiProductType: {
        list: '/product_types',
        detail: '/product_types',
        add: '/product_types',
        edit: '/product_types',
        delete: '/product_types',
        otherList: '/other_product_types',
        otherAdd: '/other_product_types',
        export: '/export'
    },

    // ==展会==
    apiPavilion: {
        productTypeList: '/pavilion/:id/product_types',
        productTypeAdd: '/pavilion/:id/product_types/bind',
        productTypeDelete: '/pavilion/:id/product_types/unwrap'
    },

    // ==数据统计==
    apiStatistics: {
        pavilion: '/statistics/pavilion',
        user: '/statistics/user',
        userDetail: '/statistics/user/detail',
    },
})
