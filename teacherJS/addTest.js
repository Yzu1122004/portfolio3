$(document).ready(function () {

    const urlParams = new URLSearchParams(window.location.search);
    const classID = urlParams.get("classID");

    if (!classID) {
        $("#classNameBox").text("未選擇班級");
        return;
    } $.get("./api/get_class_name.php?classID=" + classID, function (res) {
        const data = JSON.parse(res);
        $("#classNameBox").text(data.className || "找不到班級名稱");
    });

    // 顯示教材列表
    $.get("./api2/get_class_material_list.php?classID=" + classID, function (res) {
        const materials = JSON.parse(res);
        const table = $(".classTableBox").eq(1); // 第二個表格

        table.find("tr:gt(0)").remove(); // 清空資料列

        materials.forEach(mat => {
            const row = `
                <tr>
                    <td>${mat.name}</td>
                    <td>${mat.type}</td>
                    <td>${mat.total}</td>
                    <td class="deletStudent magnifier">
                        <img class="deletText" data-name="${mat.name}" src="./teacherIMGS/X.png" alt="">
                    </td>
                </tr>`;
            table.append(row);
        });
    });

    // 刪除教材
    $(document).on("click", ".deletText", function () {
        const name = $(this).data("name");
        if (!confirm(`確定刪除教材 "${name}" 嗎？`)) return;

        $.post("./api2/delete_material_by_name.php", { name }, function () {
            location.reload();
        });
    });
    let allMaterials = [];  // 儲存所有教材資料

    loadAllMaterials();
    function loadAllMaterials() {
        $.get("./api2/get_all_materials.php?classID=" + classID, function (res) {
    allMaterials = JSON.parse(res);
    renderMaterials(allMaterials);
});
        
    }

    function renderMaterials(materials) {
        const $table = $("#TableBox1");
        $table.find("tr:gt(0)").remove(); // 清除除了標題列之外的所有列

        materials.forEach(mat => {
            $table.append(`
      <tr>
        <td>${mat.name}</td>
        <td>${mat.type}</td>
        <td>${mat.count}</td>
        <td class="magnifier" data-name="${mat.name}" data-type="${mat.type}">
          <img src="./teacherIMGS/plus.png" alt="">
        </td>
      </tr>
    `);
        });
    }

    // 加入教材
    $(document).on("click", ".magnifier", function () {
        const name = $(this).data("name");
        const type = $(this).data("type");


        $.post("./api2/add_material_to_class.php", {
            classID: classID,
            name: name,
            type: type
        }, function (res) {
            alert("加入成功！");
        });
        location.reload();
    });

    // 搜尋功能
   $("input[type='text']").on("input", function () {
    const keyword = $(this).val().trim().toLowerCase();
    const filtered = allMaterials.filter(mat =>
         mat.name.toLowerCase().includes(keyword) || 
        mat.type.toLowerCase().includes(keyword)
    );
    renderMaterials(filtered);
});


    // 篩選功能：現有教材 / 自製教材
    $("#onlineTestButton").on("click", function () {
        $(".switchSearch div").removeClass("currentSwitch");
        $(this).addClass("currentSwitch");
        renderMaterials(allMaterials);
    });
    let currentTeacherID = null;

    $.get("./api/user_info.php", function (res) {
        const data = JSON.parse(res);
        currentTeacherID = data.teacherID;
    });

    $("#selfTestButton").on("click", function () {
        $(".switchSearch div").removeClass("currentSwitch");
        $(this).addClass("currentSwitch");
       const filtered = allMaterials.filter(mat => mat.teacherID == currentTeacherID);

        renderMaterials(filtered);
    });


})