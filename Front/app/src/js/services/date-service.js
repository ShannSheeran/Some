app.service('dateService', function() {
    // 日期列表长度
    this.dateListLength = 0;
    // 月份列表长度
    this.monthListLength = 0;

    // 日期（字符串）列表
    this.dateStrList = [];


    /**
     * 日期（对象）列表
     * @type {Array}    包括date（2016-11-24）、display（11.24）、week（四）等属性
     */
    this.dateList = [];
    /**
     * 月份（对象）列表
     * @type {Array}    包括date（2016-11-24）、year（2016）、month（11）等属性
     */
    this.monthList = [];

    /**
     * 指定月份的日期（对象）列表
     * @type {Array}    包括date（2016-11-24）、month（11）、day（24）、week（四）等属性
     */
    this.monthDateList = [];

    // 日期列表中的第一个日期字符串
    this.startDate = '';
    // 日期列表中的最后一个日期字符串
    this.endDate = '';

    /**
     * 设置日期列表长度
     * @param {int} length 数组数量
     */
    this.setDateListLength = function(length) {
        this.dateListLength = length;
    }

    /**
     * 设置月份列表长度
     * @param {int} length 数组数量
     */
    this.setMonthListLength = function(length) {
        this.monthListLength = length;
    }

    /**
     * 返回指定格式的当前时间日期
     * @param  {string} fmt 日期格式，如'yyyy-MM-dd'
     * @return {string}     指定格式的时间日期
     */
    this.getCurrentDateWithFormat = function(fmt) {
        var today = new Date();
        return today.Format(fmt);
    }

     /**
     * 返回指定年份月份指定格式的第一天
     * @param  {string} fmt 日期格式，如'/'
     * @return {string}     指定格式的时间日期
     */
    this.getFirstDay = function(year, month, fmt) {     
        return year + fmt + month + fmt + '1';
    }

    /**
     * 比较时间大小
     * @param  {string} date1 时间日期，如'yyyy-MM-dd'
     * @param  {string} date2 时间日期，如'yyyy-MM-dd'
     * @return  返回true表示第2个时间大于第一个时间
     */
    this.compareTime = function(date1, date2) {
        var oDate1 = new Date(date1);
        var oDate2 = new Date(date2);
        if (oDate1.getTime() < oDate2.getTime()) {
            return true;
        } else {
            return false;
        }
    }


    /**
     * 返回指定年月的月天数
     * @param  {string}     year 
     * @param  {string}     month 
     * @return {string}     指定年月的月天数
     */
    this.getMonthCount = function(year, month) {
        var today = new Date(year, month, 0);
        return today.getDate();
    }

    /**
     * 获取日期列表
     * @param  {string} theDate 指定日期，格式为年月（'yyyy-MM'）或年月日('yyyy-MM-dd')
     * @param  {string} type    获取类型：pre-截止至传入日期的前一天，next-从传入日期的后一天开始
     * @param  {bool} isContain 是否包含传入日期
     * @return {array}          日期列表数组
     */
    this.getDateList = function(theDate, type, isContain) {
        // 将传入日期字符串转换为日期对象
        var theDateObj = new Date(Date.parse(theDate.replace(/-/g, "/")));

        this.dateList = [];
        this.dateStrList = [];

        if (type == 'pre') {
            theDateObj.setDate(theDateObj.getDate() - this.dateListLength - 1);
            if (isContain) {
                theDateObj.setDate(theDateObj.getDate() + 1);
            }
        } else {
            if (isContain) {
                theDateObj.setDate(theDateObj.getDate() - 1);
            }
        }
        for (var i = 1; i <= this.dateListLength; i++) {
            var itemDateObj = theDateObj;
            itemDateObj.setDate(theDateObj.getDate() + 1);
            var dateStr = itemDateObj.Format('yyyy-MM-dd');
            var item = {
                'date': dateStr,
                'display': itemDateObj.Format('MM.dd'),
                'week': itemDateObj.Format('E')
            }
            this.dateStrList.push(dateStr);
            this.dateList.push(item);
        }
        this.startDate = this.dateList[0].date;
        this.endDate = this.dateList[this.dateList.length - 1].date;
        return this.dateList;
    }

    /**
     * 获取包含指定月份在内的月份列表，指定月份的前一个月+后3个月
     * @param  {string} theDate     指定日期，格式为年月（'yyyy-MM'）或年月日('yyyy-MM-dd')
     * @param  {int}  preMonthCount 列表从指定月份的前N个月开始，N可为正数或负数
     * @return {array}              日期列表数组
     */
    this.getMonthList = function(theDate, preMonthCount) {
        // 将传入日期字符串转换为日期对象
        var theDateObj = new Date(Date.parse(theDate.replace(/-/g, "/")));

        theDateObj.setDate(1);
        theDateObj.setMonth(theDateObj.getMonth() - 1);
        if (preMonthCount) {
            theDateObj.setMonth(theDateObj.getMonth() - preMonthCount);
        }

        this.monthList = [];
        for (var i = 1; i <= this.monthListLength; i++) {
            var itemDateObj = theDateObj;
            itemDateObj.setDate(1);
            itemDateObj.setMonth(theDateObj.getMonth() + 1);
            var item = {
                'date': itemDateObj.Format('yyyy-MM-dd'),
                'year': itemDateObj.Format('yyyy'),
                'month': itemDateObj.Format('MM')
            }
            this.monthList.push(item);
        }
        return this.monthList;
    }



    /**
     * 获取指定月份的日期列表
     * @param  {string} theDate 指定日期，格式为年月（'yyyy-MM'）或年月日('yyyy-MM-dd')
     * @return {array}          日期列表数组
     */
    this.getMonthDateList = function(theDate) {
        // 将传入日期字符串转换为日期对象
        var theDateObj = new Date(Date.parse(theDate.replace(/-/g, "/")));

        theDateObj.setDate(1);
        var month = theDateObj.Format('MM');
        this.monthDateList = [];
        for (var i = 1; i <= 31; i++) {
            var itemDateObj = theDateObj;
            // 默认已从1日开始，因此第一个不需要处理
            if (i > 1) {
                itemDateObj.setDate(theDateObj.getDate() + 1);
            }
            // 当日期已超过传入月份，则结束
            if (itemDateObj.Format('MM') != month) {
                break;
            }
            var item = {
                'date': itemDateObj.Format('yyyy-MM-dd'),
                'month': itemDateObj.Format('MM'),
                'day': itemDateObj.Format('d'),
                'week': itemDateObj.Format('E')
            }
            this.monthDateList.push(item);
        }
        return this.monthDateList;
    }

    // 将月份日期列表按周分组
    this.groupDateListByWeek = function(dateList) {
        var list = [];

        var day1 = dateList[0];
        var startIndex = 0;
        if (day1.week == '一') {
            startIndex = 1;
        } else if (day1.week == '二') {
            startIndex = 2;
        } else if (day1.week == '三') {
            startIndex = 3;
        } else if (day1.week == '四') {
            startIndex = 4;
        } else if (day1.week == '五') {
            startIndex = 5;
        } else if (day1.week == '六') {
            startIndex = 6;
        } else if (day1.week == '日') {
            startIndex = 7;
        }

        for (var i = 0; i < 6; i++) {
            var row = [];
            var isReturn = false;
            for (var j = 1; j <= 7; j++) {
                var item = {};
                index = i * 7 + j - startIndex;
                // 补全1日当周缺失的前N日
                if (index < 0) {
                    item.isShow = false;
                } else if (index < dateList.length) {
                    item = dateList[index];
                    item.isShow = true;
                } else if (index >= dateList.length) {
                    item.isShow = false;
                    isReturn = true;
                }
                row.push(item);
            }
            list.push(row);

            if (isReturn) {
                break;
            }
        }
        return list;
    }
})

/**
 * 对Date的扩展，将 Date 转化为指定格式的String
 * 月(M)、日(d)、12小时(h)、24小时(H)、分(m)、秒(s)、周(E)、季度(q) 可以用 1-2 个占位符
 * 年(y)可以用 1-4 个占位符，毫秒(S)只能用 1 个占位符(是 1-3 位的数字)
 * eg:
 * (new Date()).Format("yyyy-MM-dd hh:mm:ss.S") ==> 2006-07-02 08:09:04.423
 * (new Date()).Format("yyyy-MM-dd E HH:mm:ss") ==> 2009-03-10 二 20:09:04
 * (new Date()).Format("yyyy-MM-dd EE hh:mm:ss") ==> 2009-03-10 周二 08:09:04
 * (new Date()).Format("yyyy-MM-dd EEE hh:mm:ss") ==> 2009-03-10 星期二 08:09:04
 * (new Date()).Format("yyyy-M-d h:m:s.S") ==> 2006-7-2 8:9:4.18
 */
Date.prototype.Format = function(fmt) {
    var o = {
        "M+": this.getMonth() + 1, //月份
        "d+": this.getDate(), //日
        "h+": this.getHours() % 12 == 0 ? 12 : this.getHours() % 12, //小时
        "H+": this.getHours(), //小时
        "m+": this.getMinutes(), //分
        "s+": this.getSeconds(), //秒
        "q+": Math.floor((this.getMonth() + 3) / 3), //季度
        "S": this.getMilliseconds() //毫秒
    };
    var week = {
        "0": "\u65e5",
        "1": "\u4e00",
        "2": "\u4e8c",
        "3": "\u4e09",
        "4": "\u56db",
        "5": "\u4e94",
        "6": "\u516d"
    };
    if (/(y+)/.test(fmt)) {
        fmt = fmt.replace(RegExp.$1, (this.getFullYear() + "").substr(4 - RegExp.$1.length));
    }
    if (/(E+)/.test(fmt)) {
        fmt = fmt.replace(RegExp.$1, ((RegExp.$1.length > 1) ? (RegExp.$1.length > 2 ? "\u661f\u671f" : "\u5468") : "") + week[this.getDay() + ""]);
    }
    for (var k in o) {
        if (new RegExp("(" + k + ")").test(fmt)) {
            fmt = fmt.replace(RegExp.$1, (RegExp.$1.length == 1) ? (o[k]) : (("00" + o[k]).substr(("" + o[k]).length)));
        }
    }
    return fmt;
}
