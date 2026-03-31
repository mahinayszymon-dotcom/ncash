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
        // Target the body specifically – this is often the culprit!
        const modalBody = modal.querySelector('.modal-body');
        const originalBodyStyle = modalBody.style.cssText;

        // Force the OUTER modal to be a regular block that expands
        modal.style.cssText = `
            display: block !important; 
            opacity: 1 !important;
            position: absolute; 
            left: 0; 
            top: 0; 
            width: 100%; 
            height: auto !important; 
            min-height: auto !important;
            overflow: visible !important; 
            z-index: -1000;
            background: #ffffff !important; /* Ensure background is solid */
        `;

        // Force the CONTENT to expand
        modalContent.style.cssText += `
            display: block !important;
            width: 7.5in !important;
            height: auto !important;
            min-height: auto !important;
            overflow: visible !important;
            margin: 0 auto !important;
            box-shadow: none !important;
        `;

        // THE CRITICAL STEP: Reset the Modal Body
        if (modalBody) {
            modalBody.style.cssText = `
                display: block !important;
                height: auto !important; 
                max-height: none !important; 
                overflow: visible !important;
                padding: 0 !important;
            `;
        }


        //hiding the targeted elements earlier
        if (closeBtn) closeBtn.style.display = 'none';
        if (footer) footer.style.display = 'none';

        //Forcing the opacity and prevent any animation
        modalContent.style.setProperty('animation', 'none', 'important');
        modalContent.style.setProperty('opacity', '1', 'important');
        modalContent.style.setProperty('background-color', '#ffffff', 'important');

        //Forcing the styles so that it doesn't look faded
        container.querySelectorAll('*').forEach(el => {
            el.style.setProperty('opacity', '1', 'important');

            //Targets the top_analytics text to make it appear white
            if (el.classList.contains('top_analytics') || 
                el.classList.contains('top_analytics_title') || 
                el.classList.contains('top_analytics_value') ||
                el.closest('.top_analytics')) 
            {
                
                el.style.setProperty('color', '#ffffff', 'important');
            } 
            
            //Targets the table headers
            else if (el.tagName === 'TH' || el.tagName === 'THEAD') 
            {
                el.style.setProperty('background-color', '#f1eded', 'important');
                el.style.setProperty('color', '#000000', 'important');
                el.style.setProperty('-webkit-print-color-adjust', 'exact', 'important');
            } 
            
            //Targets all the other text
            else {
                el.style.setProperty('color', '#000000', 'important');
            }
        });

        await new Promise(resolve => setTimeout(resolve, 1000));

        const opt = {
            margin: 0.5,
            filename: `${branchName}_Report.pdf`,
            image: { type: 'jpeg', quality: 1 },
            html2canvas: { 
                scale: 4,              
                useCORS: true, 
                letterRendering: true,
                backgroundColor: '#ffffff' // ADD THIS: Prevents grey/transparent tints
            },
            jsPDF: { unit: 'in', format: 'letter', orientation: 'portrait' },
            pagebreak: { mode: 'css', avoid: 'tr' }
        };

        //generating the pdf file
        await html2pdf().set(opt).from(modalContent).save();

        //restoring the original stylings
        modal.style.cssText = originalModalStyle;
        modalContent.style.cssText = originalContentStyle;
        modalBody.style.cssText = originalBodyStyle;
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
    const modalBody = modal.querySelector('.modal-body');
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

        // 1. Save original styles
        const originalModalStyle = modal.style.cssText;
        const originalContentStyle = modalContent.style.cssText;
        const originalBodyStyle = modalBody.style.cssText;

        // 2. The "Ghost" Setup (Visible to script, hidden from user)
        // We use left: -9999px instead of visibility:hidden to avoid blank pages.
        modal.style.cssText = `
            display: block !important; 
            position: fixed !important; 
            left: -9999px !important; 
            top: 0 !important; 
            width: 8.5in !important; 
            height: auto !important; 
            overflow: visible !important; 
            z-index: -1000;
            background: white !important;
            opacity: 1 !important;
        `;

        modalContent.style.cssText = `
            display: block !important;
            width: 100% !important;
            height: auto !important;
            overflow: visible !important;
            background: white !important;
            opacity: 1 !important;
            box-shadow: none !important;
        `;

        if (modalBody) {
            modalBody.style.cssText = "display: block !important; height: auto !important; max-height: none !important; overflow: visible !important;";
        }

        if (closeBtn) closeBtn.style.display = 'none';
        if (footer) footer.style.display = 'none';

        // Small wait for the layout to settle
        await new Promise(resolve => setTimeout(resolve, 1200));

        const opt = {
            margin: 0.5,
            filename: `${branchName}_Report.pdf`,
            image: { type: 'jpeg', quality: 0.98 },
            html2canvas: { 
                scale: 2, 
                useCORS: true, 
                letterRendering: true,
                backgroundColor: '#ffffff', // FIXES SEMI-TRANSPARENCY
                scrollY: 0,
                scrollX: 0
            },
            jsPDF: { unit: 'in', format: 'letter', orientation: 'portrait' },
            pagebreak: { mode: ['avoid-all', 'css'], avoid: 'tr' }
        };

        // 3. Generate from modalContent
        await html2pdf().set(opt).from(modalContent).save();

        // 4. Restore original styles
        modal.style.cssText = originalModalStyle;
        modalContent.style.cssText = originalContentStyle;
        modalBody.style.cssText = originalBodyStyle;
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
    const isConfirmed = confirm(`Are you sure to turnover all records seen on the table? \n\nThis will generate a file and remove all records afterwards.`);

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
        // Target the body specifically – this is often the culprit!
        const modalBody = modal.querySelector('.modal-body');
        const originalBodyStyle = modalBody.style.cssText;

        // Force the OUTER modal to be a regular block that expands
        modal.style.cssText = `
            display: block !important; 
            visibility: hidden; 
            position: absolute; 
            left: 0; 
            top: 0; 
            width: 100%; 
            height: auto !important; 
            min-height: auto !important;
            overflow: visible !important; 
            z-index: -1000;
        `;

        // Force the CONTENT to expand
        modalContent.style.cssText += `
            display: block !important;
            width: 7.5in !important;
            height: auto !important;
            min-height: auto !important;
            overflow: visible !important;
            margin: 0 auto !important;
            box-shadow: none !important;
        `;

        // THE CRITICAL STEP: Reset the Modal Body
        if (modalBody) {
            modalBody.style.cssText = `
                display: block !important;
                height: auto !important; 
                max-height: none !important; 
                overflow: visible !important;
                padding: 0 !important;
            `;
        }


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
        modalBody.style.cssText = originalBodyStyle;
        if (closeBtn) closeBtn.style.display = 'block';
        if (footer) footer.style.display = 'flex';

        alert("Turnover Complete.\n\nRecords can still be viewed at archive and the page will now refresh.");
        location.reload(); 

    } catch (error) {
        console.error('Error:', error);
        alert("Failed to generate PDF.");
    }
}