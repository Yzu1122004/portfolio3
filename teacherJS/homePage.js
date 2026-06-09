
$(document).ready(function () {

    $(document).on("click", "#ij4jm-2", function () {
        window.location.href = "./php/logout.php";
        console.log('0');
    });
    // 顯示/隱藏表單區塊
    $("#closeChangeTeacherPage").click(() => $("#changeTeacherPage").hide());
    $("#openChangeTeacherPageButton").click(() => $("#changeTeacherPage").css("display", "flex"));
    $("#closeCreateNewClassPage").click(() => $("#createNewClassPage").hide());
    $("#createNewClass").click(() => $("#createNewClassPage").css("display", "flex"));

    // 載入教師資料與班級列表
    function loadTeacherData() {

        $.get("./api/teacherHome/teacher.php", function (data) {
            const res = JSON.parse(data);
            $("#teacherName p").text(res.name);
            $(".classRoom").empty();

            $("#teacherAvatarImg")
                .css("background-image", `url(${res.userIMG})`)
            console.log(res.userIMG);
            $("#teacherAvatarImg").css("background-image", `url('${res.userIMG}?t=${Date.now()}')`)

            res.classes.forEach(cls => {
                $(".classRoom").append(`
                    <div class="classRoomName" data-classid="${cls.classID}">${cls.className}</div>
                `);
            });
        });
        $("#img").on("change", function () {
            const file = this.files[0];
            if (file && file.type.startsWith("image/")) {
                const reader = new FileReader();
                reader.onload = function (e) {
                    $("#teacherAvatarImg").css("background-image", `url(${e.target.result})`);
                };
                reader.readAsDataURL(file);
            }
        });
    }
    loadTeacherData();

    // 更改名稱
    $("form.changeTeacherPageBox").submit(function (e) {
        e.preventDefault();
        const newName = $("#name").val();
        if (newName != '')
            $.post("./api/teacherHome/update_name.php", { name: newName }, function () {
                location.reload();
            });

        const imgFile = $("#img")[0].files[0];
        const formData = new FormData();
        formData.append("name", newName);
        if (imgFile) {
            formData.append("img", imgFile);
        }

        $.ajax({
            url: "./api/teacherHome/update_profile.php",
            method: "POST",
            data: formData,
            processData: false,
            contentType: false,
            success: function () {
                alert("更新成功");
                location.reload();
            },
            error: function (xhr) {
                alert("更新失敗：" + xhr.responseText);
            }
        });
    });



    // 新增班級
    $("#createNewClassPage form").submit(function (e) {
        e.preventDefault();
        const className = $("#classname").val();
        $.post("./api/teacherHome/create_class.php", { classname: className }, function () {
            loadTeacherData(); // 重新載入班級清單
            $("#createNewClassPage").hide();
        });
    });

    // 點擊班級 → 載入詳細資料
    $(document).on("click", ".classRoomName", function () {
        $('.notFound').hide();
        $(".classRoomName").removeClass("currentClassRoom");
        $(this).addClass("currentClassRoom");

        const className = $(this).text().trim();
        const classID = $(this).data("classid");

        $.get(`./api/teacherHome/get_class_info.php?classID=${classID}`, function (res) {
            const data = JSON.parse(res);

            $("#classNameBox").text(className);
            $("#classID").html(`<p>班級代碼 &nbsp : &nbsp</p>${data.classID}`);
            $("#crewNumber").html(`<p>人數 &nbsp : &nbsp</p>${data.studentCount}`);

            const table = $(".classTableBox");
            table.find("tr:gt(0)").remove();

            data.students.forEach(stu => {
                table.append(`
                    <tr class="studentRow" data-studentid="${stu.UserID}">
                        <td>${stu.Name}</td>
                        <td>${stu.Account}</td>
                        <td>
                            <img class="deletStudent X_img deletStudentButton" src="./teacherIMGS/X.png" alt="notFound">
                        </td>
                    </tr>
                `);

            });

        });
    });

    // 刪除學生
    $(document).on("click", ".deletStudentButton", function () {
        const row = $(this).closest("tr");
        const studentID = row.data("studentid");
        const classID = $(".classRoomName.currentClassRoom").data("classid");
        console.log('0')
        if (!confirm("確定要刪除這位學生嗎？")) return;

        $.post("./api/teacherHome/delete_student.php", {
            classID: classID,
            studentID: studentID
        }, function () {
            row.remove();
            const current = parseInt($("#crewNumber").text().replace(/[^0-9]/g, ""));
            $("#crewNumber").html(`<p>人數 &nbsp : &nbsp</p>${current - 1}`);
        });
    });

    $(document).on("click", ".deleteClassBtn", function () {
        const $current = $(".classRoomName.currentClassRoom");
        const classID = $current.data("classid");
        console.log('0')
        if (!classID) {
            alert("請先選擇要刪除的班級");
            return;
        }

        const confirmDelete = confirm(`確定要刪除班級 "${$current.text()}" 嗎？刪除後無法復原！`);
        if (!confirmDelete) return;

        $.post("./api/teacherHome/delete_class.php", { classID: classID }, function (res) {
            alert("已刪除班級");
            location.reload(); // 重新整理頁面，或改為呼叫更新教師班級列表函數
        });
    });

});
