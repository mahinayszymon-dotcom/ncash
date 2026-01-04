function fetchLoan() {
    const select = document.getElementById('agreement_num');
    const item_id = select.value;
    const item_disp = document.getElementById('item_name');
    const client_disp = document.getElementById('client_name');
    const interest_disp = document.getElementById('interest');
    const principal_disp = document.getElementById('principal');

    if (agreement_num === "") 
    {
        principal_disp.value = "₱ ";
        interest_disp.value = "₱ ";
        return;
    }

    const formData = new FormData();
    formData.append('agreement_num', item_id);

    fetch('../../db/loan_fetch.php', 
    { 
        method: 'POST',
        body: formData
    })
    .then(response => {
        if (!response.ok) {
            throw new Error('HTTP error! Status: ' + response.status + ' (' + response.statusText + ')');
        }
        return response.json();
    })
    .then(data => 
    {
        if (data.success) 
        {
            client_disp.value = data.fullname;
            item_disp.value = data.item_name;
            principal_disp.value = `₱ ${parseFloat(data.principal).toFixed(0)}`;
            interest_disp.value = `₱ ${parseFloat(data.interest).toFixed(0)}`;
        } 
        else 
        {
            principal_disp.value = "Not Found";
            interest_disp.value = "Not Found";
            console.error(data.message);
        }
    })
    .catch(error => 
    {
        console.error('Error fetching details:', error);
        principal_disp.value = "Error";
        interest_disp.value = "Error";
    });
}