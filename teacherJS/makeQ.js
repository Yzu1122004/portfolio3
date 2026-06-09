//makeTest
$("#updateTest ,#updateVideo").click(function () {
    $("#updateTest ,#updateVideo").removeClass("currentUpdateButton");
    $(this).addClass("currentUpdateButton");
})

$("#updateTest").click(function () {
    
      $("#updateVideoPage").css("opacity","0");
      $("#yourText").css("width","60%");
     $(".textGroupBox").css("width"," calc(40% - 20px)");
     $(".textGroupBox").css("opacity","1");
      $("#yourText p").show();
      $("#yourText p").css("opacity","1");;
     setTimeout(() => {
         $(".textGroupBox").show();
          $("#updateVideoPage").hide();
     }, 300);
})

$("#updateVideo").click(function () {
     $("#updateVideoPage").show();
      $("#updateVideoPage").css("opacity","1");
     $("#yourText p").css("opacity","0");;
    $("#yourText").css("width","100%");
     $(".textGroupBox").css("width","0%");
     $(".textGroupBox").css("opacity","0%");
     setTimeout(() => {
         $(".textGroupBox").hide();
          $("#yourText p").hide();
     }, 400);

})

$(".deletText").click(function () {
    $(this).closest(".inputBlock").remove();
});

$(".textBlock").on("dragstart", function (e) {
    e.originalEvent.dataTransfer.setData("text/plain", "textBlock");

});

// 進入可放置區，允許 drop
$("#yourText").on("dragover", function (e) {
    e.preventDefault(); // 很重要，否則 drop 事件不會觸發
});


// 放下物件
$("#textD").on("dragstart", function (e) {
    e.originalEvent.dataTransfer.setData("text/plain", "textD");
});
$("#imgD").on("dragstart", function (e) {
    e.originalEvent.dataTransfer.setData("text/plain", "imgD");
});
$("#fillD").on("dragstart", function (e) {
    e.originalEvent.dataTransfer.setData("text/plain", "fillD");
});
$("#mutipleChooseD").on("dragstart", function (e) {
    e.originalEvent.dataTransfer.setData("text/plain", "mutipleChooseD");
});
$("#chooseD").on("dragstart", function (e) {
    e.originalEvent.dataTransfer.setData("text/plain", "chooseD");
});
let questionCounter = 0; // 為每一題產生唯一 ID

$("#yourText").on("drop", function (e) {
    e.preventDefault();
    $("#yourText p").hide();
    const draggedId = e.originalEvent.dataTransfer.getData("text/plain");

    let newBlock = "";
    const uniqueName = "chooce_" + questionCounter; // 唯一名稱避免互相干擾

    switch (draggedId) {
        case 'textD':
            newBlock = `<div class="inputBlock quationBlock anime1">
                <input type="text" placeholder="填寫題目敘述">
                <img class="deletText" src="./teacherIMGS/X.png" alt="">
            </div>`;
            break;

        case 'fillD':
            newBlock = `<div class="inputBlock qansBlock ansBlock anime1">
                <input type="text" placeholder="填寫填空題答案">
                <img class="deletText" src="./teacherIMGS/X.png" alt="">
            </div>`;
            break;

        case 'chooseD':
            newBlock = `<div class="inputBlock ansBlock CAnsBlock anime2">
                <div class="CAnsBox">
                    <table>
                        <tr><td> (A)</td><td> <input type="text"></td></tr>
                        <tr><td> (B)</td><td> <input type="text"></td></tr>
                        <tr><td> (C)</td><td> <input type="text"></td></tr>
                        <tr><td> (D)</td><td> <input type="text"></td></tr>
                    </table>
                </div>
                <div class="ansBox">
                    答案  <table>
                        <tr><td> (A)</td><td><input type="radio" name="${uniqueName}" value="a"></td></tr>
                        <tr><td> (B)</td><td><input type="radio" name="${uniqueName}" value="b"></td></tr>
                        <tr><td> (C)</td><td><input type="radio" name="${uniqueName}" value="c"></td></tr>
                        <tr><td> (D)</td><td><input type="radio" name="${uniqueName}" value="d"></td></tr>
                    </table>
                </div>
                <img class="deletText" src="./teacherIMGS/X.png" alt="">
            </div>`;
            break;

        case 'mutipleChooseD':
            newBlock = `<div class="inputBlock ansBlock CAnsBlock anime2">
                <div class="CAnsBox">
                    <table>
                        <tr><td> (A)</td><td><input type="text"></td></tr>
                        <tr><td> (B)</td><td><input type="text"></td></tr>
                        <tr><td> (C)</td><td><input type="text"></td></tr>
                        <tr><td> (D)</td><td><input type="text"></td></tr>
                    </table>
                </div>
                <div class="ansBox">
                    答案  <table>
                        <tr><td> (A)</td><td><input type="checkbox" name="mchooce_${questionCounter}" value="a"></td></tr>
                        <tr><td> (B)</td><td><input type="checkbox" name="mchooce_${questionCounter}" value="b"></td></tr>
                        <tr><td> (C)</td><td><input type="checkbox" name="mchooce_${questionCounter}" value="c"></td></tr>
                        <tr><td> (D)</td><td><input type="checkbox" name="mchooce_${questionCounter}" value="d"></td></tr>
                    </table>
                </div>
                <img class="deletText" src="./teacherIMGS/X.png" alt="">
            </div>`;
            break;
    }

    $("#yourText").append(newBlock);
    questionCounter++;

    // 確保刪除按鈕可用
    $(".deletText").click(function () {
        $(this).closest(".inputBlock").remove();
    });
});


const $fileInput = $("#fileInput");
const $dropZone = $("#dropZone");
const $preview = $("#preview");

// 共用的處理檔案邏輯（限制一個檔案）
function handleFile(file) {
    if (!file) return;

    $preview.empty();
    if (file.type.startsWith("image/")) {
        const reader = new FileReader();
        reader.onload = function (e) {
            $preview.html(`<img src="${e.target.result}" width="150">`);
        };
        reader.readAsDataURL(file);
    } else {
        $preview.text("已選擇檔案：" + file.name);
    }
}

// input[type="file"] 選擇事件
$fileInput.on("change", function () {
    const file = this.files[0];
    uploadedFile = file; // ✅ 將選擇的檔案也存入 uploadedFile
    handleFile(file);
});


// 拖拉區阻止預設行為 + 設定樣式
$dropZone.on("dragover dragenter", function (e) {
    e.preventDefault();
    $dropZone.css("background", "#eee");
}).on("dragleave drop", function (e) {
    e.preventDefault();
    $dropZone.css("background", "");
});

// 處理拖曳檔案
$dropZone.on("drop", function (e) {
    e.preventDefault();
    $dropZone.css("background", "");

    const files = e.originalEvent.dataTransfer.files;
    if (files.length > 1) {
        alert("只能上傳 1 個檔案！");
        return;
    }


    uploadedFile = files[0]; // ⬅️ 儲存檔案
    handleFile(uploadedFile);

});
let uploadedFile = null;
$("#returnButton").click(function () {
    const materialName = $("#test_name").val().trim();
    if (!materialName) return alert("請輸入教材名稱");

    if (uploadedFile) {
        // ✅ 有影片檔案，處理影片上傳
        const formData = new FormData();
        formData.append("name", materialName);
        formData.append("videoFile", uploadedFile);

        $.ajax({
            url: "./api2/makeQ/upload_video.php",
            method: "POST",
            data: formData,
            contentType: false,
            processData: false,
            success: function (res) {
                alert("影片上傳成功！");
                location.reload();
            },
            error: function (xhr) {
                alert("影片上傳失敗：" + xhr.responseText);
            }
        });

    } else {
        // ✅ 沒有影片，是題目上傳
        const questions = [];

        $("#yourText .inputBlock").each(function (index) {
            const $el = $(this);
            const q = {
                type: "",
                answer: null,
                a: null, b: null, c: null, d: null,
                text: null,
                materialID: index + Math.floor(Math.random() * 10000) + Date.now()
            };

            if ($el.find("input[type='text']").length === 1 && !$el.hasClass("ansBlock")) {
                q.type = "題目文字";
                q.text = $el.find("input").val();
            } else if ($el.hasClass("qansBlock")) {
                q.type = "填空";
                q.text = $el.find("input").val();
            } else if ($el.hasClass("CAnsBlock") && $el.find("input[type='radio']").length) {
                q.type = "單選";
                const opts = $el.find(".CAnsBox input");
                q.a = opts.eq(0).val();
                q.b = opts.eq(1).val();
                q.c = opts.eq(2).val();
                q.d = opts.eq(3).val();
                const selected = $el.find(".ansBox input[type='radio']:checked");
                q.answer = selected.length ? ["a", "b", "c", "d"][selected.parent().parent().index()] : null;
            } else if ($el.hasClass("CAnsBlock") && $el.find("input[type='checkbox']").length) {
                q.type = "多選";
                const opts = $el.find(".CAnsBox input");
                q.a = opts.eq(0).val();
                q.b = opts.eq(1).val();
                q.c = opts.eq(2).val();
                q.d = opts.eq(3).val();
                const selected = [];
                $el.find(".ansBox input[type='checkbox']").each(function (i) {
                    if ($(this).prop("checked")) selected.push(["a", "b", "c", "d"][i]);
                });
                q.answer = selected.join(",");
            }

            questions.push(q);
        });

        $.ajax({
            url: "./api2/makeQ/upload_questions.php",
            type: "POST",
            contentType: "application/json",
            data: JSON.stringify({ materialName, questions }),
            success: function (res) {
                alert("上傳成功！");
                location.reload();
            },
            error: function (xhr) {
                alert("上傳失敗：" + xhr.responseText);
            }
        });
    }
});




