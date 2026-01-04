document.addEventListener("DOMContentLoaded", function () {
    
    // account.php
    const form = document.querySelector(".editable_profile_section");

    if (form) { 
        const inputs = form.querySelectorAll(".profile_tags");
        const btnEdit = form.querySelector(".account_info_detail_btn button:first-child");
        const btnSave = form.querySelector(".account_info_detail_btn button:last-child");

        let originalValues = [];
        inputs.forEach(i => originalValues.push(i.value));

        btnEdit.addEventListener("click", function (e) {
            e.preventDefault();

            if (btnEdit.textContent === "Edit") {
                inputs.forEach(i => {
                    i.disabled = false;
                    i.required = true;
                });
                btnSave.disabled = false;
                btnEdit.textContent = "Cancel";
            } else {
                inputs.forEach((i, index) => {
                    i.value = originalValues[index];
                    i.disabled = true;
                });
                btnSave.disabled = true;
                btnEdit.textContent = "Edit";
            }
        });
    }

    // item_details.php
    const form2 = document.querySelector(".editable_item_section");

    if (form2) { 
        const inputs2 = form2.querySelectorAll(".item_tags");
        const btnEdit2 = form2.querySelector(".item_info_detail_btn button:first-child"); 
        const btnSave2 = form2.querySelector(".item_info_detail_btn button:last-child");

        let originalValues2 = [];
        inputs2.forEach(i => originalValues2.push(i.value));

        btnEdit2.addEventListener("click", function (e) {
            e.preventDefault();

            if (btnEdit2.textContent === "Edit") {
                inputs2.forEach(i => {
                    i.disabled = false;
                    i.required = true;
                });
                btnSave2.disabled = false;
                btnEdit2.textContent = "Cancel";
            } else {
                inputs2.forEach((i, index) => {
                    i.value = originalValues2[index];
                    i.disabled = true;
                });
                btnSave2.disabled = true;
                btnEdit2.textContent = "Edit";
            }
        });
    }
});