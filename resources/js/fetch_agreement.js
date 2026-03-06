function updateAgreement() 
{
        const branchSelect = document.getElementById('branch_select');
        const branchId = branchSelect.value;
        const agreement_disp = document.getElementById('agreement_num');

        if (!branchId) {
            agreement_disp.value = 'Error';
            return;
        }

        fetch('../../db/agreement_fetch.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded'
            },
            body: 'branch_id=' + encodeURIComponent(branchId)
        })
            .then(response => response.json())
            .then(data => {
                if (data.error) 
                {
                    console.error(data.error);
                    agreement_disp.value = 'Error';
                } 
                else
                {
                    agreement_disp.value = data.next_agreement;
                }
            })
            .catch(error => {
                console.error('Fetch error:', error);
                agreement_disp.value = 'Error';
            });
}