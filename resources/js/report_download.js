async function downloadPdf(branchName, branchId) {
    const modal = document.getElementById('reportModal');
    const title = document.getElementById('modalBranchName');
    const container = document.getElementById('modalDataContainer');
    const modalContent = modal.querySelector('.modal-content');
    
    //Choose the elements that need to be hidden
    const closeBtn = modal.querySelector('.close_button');
    const footer = modal.querySelector('.modal-footer');

    title.innerText = branchName + " Detailed Report";
    container.innerHTML = "<p>Preparing PDF data...</p>";

    try {
        const formData = new FormData();
        formData.append('branch_id', branchId);

        const response = await fetch('../db/fetch_branch_data.php', {
            method: 'POST',
            body: formData
        });
        const html = await response.text();
        container.innerHTML = html;

        //saving original styles
        const originalModalStyle = modal.style.cssText;
        const originalContentStyle = modalContent.style.cssText;

        //make the modal visible to the system but not the user
        modal.style.cssText = "display: flex; visibility: hidden; position: fixed; z-index: -1000; pointer-events: none;";
        
        //styling the modal's size (to closely match letter size) and visibility
        modalContent.style.cssText += `
            opacity: 1 !important; 
            filter: none !important; 
            box-shadow: none !important;
            width: 7.5in !important;
            margin: 0 auto !important;
            display: block !important;
        `;

        //hiding the targeted elements earlier
        if (closeBtn) closeBtn.style.display = 'none';
        if (footer) footer.style.display = 'none';

        await new Promise(resolve => setTimeout(resolve, 1000));

        const opt = {
            margin: 0.5,
            filename: `${branchName}_Report.pdf`,
            image: { type: 'jpeg', quality: 1 },
            html2canvas: { scale: 2, useCORS: true, letterRendering: true },
            jsPDF: { unit: 'in', format: 'letter', orientation: 'portrait' },
            pagebreak: { mode: 'css', avoid: 'tr' }
        };

        //generating the pdf file
        await html2pdf().set(opt).from(modalContent).save();

        //restoring the original stylings
        modal.style.cssText = originalModalStyle;
        modalContent.style.cssText = originalContentStyle;
        if (closeBtn) closeBtn.style.display = 'block';
        if (footer) footer.style.display = 'flex';

    } catch (error) {
        console.error('Error:', error);
        alert("Failed to generate PDF.");
    }
}

function prepReport() {
    const btn = document.getElementById('generate');
    
    const branchName = btn.getAttribute('data-branch-name');
    const branchId = btn.getAttribute('data-branch-id');

    const startDate = document.getElementById('begin_date').value;
    const endDate = document.getElementById('end_date').value;


    //Trigger function below original async function
    generateCustom(branchName, branchId, startDate, endDate);
}

async function generateCustom(branchName, branchId, startDate, endDate) {
    const modal = document.getElementById('reportModal');
    const title = document.getElementById('modalBranchName');
    const container = document.getElementById('modalDataContainer');
    const modalContent = modal.querySelector('.modal-content');
    
    //Choose the elements that need to be hidden
    const closeBtn = modal.querySelector('.close_button');
    const footer = modal.querySelector('.modal-footer');

    title.innerText = branchName + " Detailed Report";
    container.innerHTML = "<p>Preparing PDF data...</p>";

    try {
        const formData = new FormData();
        formData.append('branch_id', branchId);
        formData.append('start_date', startDate);
        formData.append('end_date', endDate);

        const response = await fetch('../db/fetch_branch_ranged.php', {
            method: 'POST',
            body: formData
        });
        const html = await response.text();
        container.innerHTML = html;

        //saving original styles
        const originalModalStyle = modal.style.cssText;
        const originalContentStyle = modalContent.style.cssText;

        //make the modal visible to the system but not the user
        modal.style.cssText = "display: flex; visibility: hidden; position: fixed; z-index: -1000; pointer-events: none;";
        
        //styling the modal's size (to closely match letter size) and visibility
        modalContent.style.cssText += `
            opacity: 1 !important; 
            filter: none !important; 
            box-shadow: none !important;
            width: 7.5in !important;
            margin: 0 auto !important;
            display: block !important;
        `;

        //hiding the targeted elements earlier
        if (closeBtn) closeBtn.style.display = 'none';
        if (footer) footer.style.display = 'none';

        await new Promise(resolve => setTimeout(resolve, 1000));

        const opt = {
            margin: 0.5,
            filename: `${branchName}_Report.pdf`,
            image: { type: 'jpeg', quality: 1 },
            html2canvas: { scale: 2, useCORS: true, letterRendering: true },
            jsPDF: { unit: 'in', format: 'letter', orientation: 'portrait' },
            pagebreak: { mode: 'css', avoid: 'tr' }
        };

        //generating the pdf file
        await html2pdf().set(opt).from(modalContent).save();

        //restoring the original stylings
        modal.style.cssText = originalModalStyle;
        modalContent.style.cssText = originalContentStyle;
        if (closeBtn) closeBtn.style.display = 'block';
        if (footer) footer.style.display = 'flex';

    } catch (error) {
        console.error('Error:', error);
        alert("Failed to generate PDF.");
    }
}

//turnover logic
function prepLiquid() {
    const btn = document.getElementById('turnover');
    
    const lqRole = btn.getAttribute('data-role');
    const lqBranch = btn.getAttribute('data-branch');

    //Performing confirmation alert
    const isConfirmed = confirm(`Are you sure to turnover all records seen on the table? \n\nThis will generate a file a remove all records afterwards.`);

    //Function will only run if alert was confirmed
    if (isConfirmed) {
        generateLiquid(lqRole, lqBranch);
    } 
}

async function generateLiquid(userRole, liquidBranch) {
    const modal = document.getElementById('reportModal');
    const title = document.getElementById('modalBranchName');
    const container = document.getElementById('modalDataContainer');
    const modalContent = modal.querySelector('.modal-content');
    
    //Choose the elements that need to be hidden
    const closeBtn = modal.querySelector('.close_button');
    const footer = modal.querySelector('.modal-footer');

    const branchName = liquidBranch.charAt(0).toUpperCase() + liquidBranch.slice(1);

    title.innerText = branchName + " Turnover Report";
    container.innerHTML = "<p>Preparing PDF data...</p>";

    try {
        const formData = new FormData();
        formData.append('lq_role', userRole);
        formData.append('lq_branch', liquidBranch);

        const response = await fetch('../db/fetch_liquidated.php', {
            method: 'POST',
            body: formData
        });
        const html = await response.text();
        container.innerHTML = html;

        //saving original styles
        const originalModalStyle = modal.style.cssText;
        const originalContentStyle = modalContent.style.cssText;

        //make the modal visible to the system but not the user
        modal.style.cssText = "display: flex; visibility: hidden; position: fixed; z-index: -1000; pointer-events: none;";
        
        //styling the modal's size (to closely match letter size) and visibility
        modalContent.style.cssText += `
            opacity: 1 !important; 
            filter: none !important; 
            box-shadow: none !important;
            width: 7.5in !important;
            margin: 0 auto !important;
            display: block !important;
        `;

        //hiding the targeted elements earlier
        if (closeBtn) closeBtn.style.display = 'none';
        if (footer) footer.style.display = 'none';

        await new Promise(resolve => setTimeout(resolve, 1000));

        const opt = {
            margin: 0.5,
            filename: `${branchName}_Report.pdf`,
            image: { type: 'jpeg', quality: 1 },
            html2canvas: { scale: 2, useCORS: true, letterRendering: true },
            jsPDF: { unit: 'in', format: 'letter', orientation: 'portrait' },
            pagebreak: { mode: 'css', avoid: 'tr' }
        };

        //generating the pdf file
        await html2pdf().set(opt).from(modalContent).save();

        //restoring the original stylings
        modal.style.cssText = originalModalStyle;
        modalContent.style.cssText = originalContentStyle;
        if (closeBtn) closeBtn.style.display = 'block';
        if (footer) footer.style.display = 'flex';

        alert("Turnover Complete.\n\nRecords can still be viewed at archive and the page will now refresh.");
        location.reload(); 

    } catch (error) {
        console.error('Error:', error);
        alert("Failed to generate PDF.");
    }
}