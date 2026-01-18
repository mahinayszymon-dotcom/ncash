function fetchLoan() {
    const select = document.getElementById('agreement_num');
    const item_id = select.value;
    const item_disp = document.getElementById('item_name');
    const client_disp = document.getElementById('client_name');
    const interest_disp = document.getElementById('interest');
    const principal_disp = document.getElementById('principal');

    const penalty_inp = document.getElementById('penalty');
    const discount_inp = document.getElementById('discount');

    if (item_id === "") 
    {
        principal_disp.value = "₱ ";
        interest_disp.value = "₱ ";
        penalty_inp.disabled = true;
        discount_inp.disabled = true;
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
            interest_disp.setAttribute('data-base-val', data.interest);
            principal_disp.setAttribute('data-base-val', data.principal);

            client_disp.value = data.fullname;
            item_disp.value = data.item_name;
            principal_disp.value = `₱ ${parseFloat(data.principal).toFixed(0)}`;
            interest_disp.value = `₱ ${parseFloat(data.interest).toFixed(0)}`;

            penalty_inp.disabled = false;
            discount_inp.disabled = false;
        } 
        else 
        {
            principal_disp.value = "Not Found";
            interest_disp.value = "Not Found";

            penalty_inp.disabled = true;
            discount_inp.disabled = true;
            console.error(data.message);
        }
    })
    .catch(error => 
    {
        console.error('Error fetching details:', error);
        principal_disp.value = "Error";
        interest_disp.value = "Error";

        penalty_inp.disabled = true;
        discount_inp.disabled = true;
    });
}

function updateTotals() {
    const interestDisp = document.getElementById('interest');
    const principalDisp = document.getElementById('principal');
    const penaltyInp = document.getElementById('penalty');
    const discountInp = document.getElementById('discount');

    // Get original values
    const interest = parseFloat(interestDisp.getAttribute('data-base-val')) || 0;
    const principal = parseFloat(principalDisp.getAttribute('data-base-val')) || 0;

    const days = parseFloat(penaltyInp.value) || 0;
    const discount = parseFloat(discountInp.value) || 0;

    //1% of Principal per day
    const penaltyAmt = principal * 0.01 * days;

    const newInterest = interest + penaltyAmt - discount;
    interestDisp.value = `₱ ${newInterest.toFixed(0)}`;

    const newPrincipal = principal + penaltyAmt - discount;
    principalDisp.value = `₱ ${newPrincipal.toFixed(0)}`
}

document.getElementById('penalty').addEventListener('input', updateTotals);
document.getElementById('discount').addEventListener('input', updateTotals);