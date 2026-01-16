/**
 * @type {import("../../lib/dtpweb").DTPWeb}
 */
var api = dtpweb.getInstance({
    // jsonMode: false,
});
//
window.onload = function () {
    // 检测打印接口是否可用
    api.checkPlugin(function (success) {
        if (success) {
            updatePrinterList();
        } else {
            api = undefined;
            alert("未检测到打印机插件！");
        }
    });
   
    document.getElementById("test-open-printer").onclick = function () {
        onOpenPrinter();
    };
    document.getElementById("test-close-printer").onclick = function () {
        onClosePrinter();
    };
};

//#region 获取打印机列表

/**
 * 更新打印机列表。
 */
function updatePrinterList() {
    var printerElements = document.getElementById("select-printlist");

    // 为了避免打印的时候，数据打印不完全的问题，js接口中采用的是ajax同步请求方式；
    // 为了避免服务未打开的时候，调用接口时出现假死状态，在合适的地方调用皆苦前最好先检测下url是否可用；
    if (api) {
        var printers = api.getPrinters({ onlyLocal: false });
        // 先清空当前打印机列表
        printerElements.innerHTML = "";
        // 重新添加打印机列表
        if (printers instanceof Array && printers.length > 0) {
            for (var i = 0; i < printers.length; i++) {
                var item = printers[i];
                // 如果检测到局域网内的其他打印机，则可以获取ip和hostname，如果是本地打印机，则参数中只有name属性，表示打印机名称；
                var name = api.isLocalPrinter(item) ? item.name : item.name + "@" + item.hostname;
                var value = api.isLocalPrinter(item) ? item.name : item.name + "@" + item.ip;
                printerElements.options.add(new Option(name, value));
            }
        } else {
            printerElements.options.add(new Option("未检测到打印机", ""));
        }
        // 链接默认打印机，并更新选中打印机打印参数；
        // onPrinterChanged();
    }
}


/**
 * 获取当前任务的图片信息；
 */
function showJobPages(pages) {
    if (isPrintJob()) return;
    // 先清空预览区域；
    clearPreview();
    //
    if (pages && pages.length > 0) {
        for (var i = 0; i < pages.length; i++) {
            addPreview(pages[i]);
        }
    } else {
        // 如果需要显示打印任务中所有的标签，需要先查获取标签的页数。
        var info = api.getPageInfo();
        // 遍历所有页面数据，然后添加到预览区域
        if (info) {
            for (var i = 0; i < info.pages; i++) {
                var page = api.getPageImage({ page: i });
                addPreview(page.data);
            }
        }
    }
}

/**
 * 获取当前选中的打印机；
 */
function getCurrPrinter() {
    var printerElement = document.getElementById("select-printlist");
    if (!printerElement.value) return {};

    var arr = printerElement.value.split("@");
    return {
        printerName: arr[0],
        ip: arr[1],
    };
}

//#endregion

/**
 * 打开当前打印机；
 */
function openPrinter(callback) {
    var printer = getCurrPrinter();
    if (printer.printerName && api) {
        api.openPrinter(printer, callback);
    } else if (callback) {
        callback(false);
    }
}

function onOpenPrinter() {
    var printer = getCurrPrinter();
    if (!printer.printerName) {
        alert("未检测到打印机");
        return false;
    }
    api.openPrinter(printer, function (success) {
        if (success) {
            alert("打印机打开成功");
        } else {
            alert("打印机打开失败");
        }
    });
}

/**
 * 关闭已连接打印机；
 */
function onClosePrinter() {
    api.closePrinter();
}

/**
 * 当打印机更新后，同步的更新当前打印机的相关打印参数；
 */
function onPrinterChanged() {
    openPrinter(function (success) {
        if (success) {
            var gapTypeSelect = document.getElementById("select-gaptype");
            gapTypeSelect.value = api.getGapType();

            var printSpeed = document.getElementById("select-printspeed");
            printSpeed.value = api.getPrintSpeed();

            var printDarkness = document.getElementById("select-printdarkness");
            printDarkness.value = api.getPrintDarkness();
            // 使用完毕后，关闭打印机，避免占用打印机，影响其他用户的使用；
            api.closePrinter();
        }
    });
}

/**
 * 修改当前打印机的纸张类型；
 * 打印机打开成功后调用有效；
 */
function onGapTypeChanged() {
    var gapTypeSelect = document.getElementById("select-gaptype");
    api.setGapType(gapTypeSelect.value);
}

/**
 * 修改当前打印机的打印速度；
 * 打印机打开成功后调用有效；
 */
function onPrintSpeedChanged() {
    var printSpeed = document.getElementById("select-printspeed");
    api.setPrintSpeed(printSpeed.value);
}

/**
 * 修改当前选中打印机的打印浓度；
 * 打印机打开成功后调用有效；
 */
function onPrintDarknessChanged() {
    var printDarkness = document.getElementById("select-printdarkness");
    api.setPrintDarkness(printDarkness.value);
}

/**
 * 获取当前选中的任务类型值；
 * 0 ：表示当前的打印任务为打印任务；
 * 1 ： 表示当前的打印任务为白底预览任务；
 * 2 ： 表示当前的打印任务为透明底色的预览任务；
 */
function getJobTypeValue() {
    return 0;
}

/**
 * 获取当前打印任务名称；
 */
function getJobName(jobTypeValue, defJobName) {
    var value = typeof jobTypeValue === "number" ? jobTypeValue : getJobTypeValue();
    if (value === "1")
        // 白色底色
        return "#!#Preview#!#";
    else if (value === "2")
        // 透明底色
        return "#!#Transparent#!#";
    else return defJobName || "DTPWeb"; // 打印任务名称，随便写；
}
function getJobAction() {
    var value = typeof jobTypeValue === "number" ? jobTypeValue : getJobTypeValue();
    if (value === "1") return 0x02;
    else if (value === "2") return 0x82;
    else return 0x1000;
}
/**
 * 判断当前任务是不是打印任务。
 * true : 表示当前任务是打印任务；
 * false: 表示当前任务是预览任务；
 */
function isPrintJob() {
    var jobType = getJobTypeValue();
    return jobType !== "1" && jobType !== "2";
}


/**
 * 打印文本相关对象。
 */
function drawTextTest(content,usermark,date) {
    // 打开打印机
    openPrinter(function (success) {
        if (success) {
            var width = 40;
            var height = 30;
            var orientation = 0;
            var jobName = 'TK' + Math.random(10000,99999);
            var fontHeight = 3;
            var text = content;
            var mark = '唛头：' + usermark;
            var margin = 1;
            console.log(3456,76655)
            if (api.startJob({ width: width, height: height, orientation: orientation, jobName: jobName })) {
                //先画出条形码
                api.drawText({ text: mark,x:1,y:1, width: width, height: height, fontHeight: fontHeight });
                api.draw1DBarcode({
                    text: text,
                    x: 1,
                    y: 6,
                    width: width - margin * 2,
                    height: 15,
                    textHeight: 3,
                    FontHeight: fontHeight,
                });
                api.drawText({ text: date,x:1,y:23, width: width, height: height, fontHeight: fontHeight });
                
                api.commitJob(function () {
                    // 则显示预览效果
                    showJobPages();
                });
            }
        }
    });
}



// 打印机属性判断

function onGetPrinterName() {
    if (!api.isPrinterOpened()) {
        alert("打印机未打开");
        return false;
    }
    //
    alert(api.getPrinterName());
}
function onGetPrinterDpi() {
    if (!api.isPrinterOpened()) {
        alert("打印机未打开");
        return false;
    }
    //
    var dpi = api.getPrinterDPI();
    alert(dpi ? JSON.stringify(dpi) : dpi);
}

function onIsPrinterOpened() {
    alert(api.isPrinterOpened());
}

var toggleTag = false;
/**
 * 打开打印机属性对框框测试；
 */
function onShowProperty() {
    var printer = getCurrPrinter();
    if (!printer) {
        alert("未检测到打印机");
        return;
    }
    api.showProperty({
        showDocument: toggleTag,
        printerName: printer.printerName,
    });
    // 切换 toggle状态，分别测试 showDocument为true和false；
    toggleTag = !toggleTag;
}
