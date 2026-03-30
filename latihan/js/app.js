// Fungsi untuk tampil murid ke UI
function tampilkanMurid() {
    const list = document.getElementById("listMurid");

    // kosongkan dulu isi list
    list.innerHTML = "";

    // loop semua data murid
    murid.foreach((m) => {
        const item = document.createElement("li");

        item.className = "list-group-item";
        item.innerText = m.nama;

        list.appendChild(item);
    })
}

tampilkanMurid();

// Ambil data transaksi dari localStorage
let transaksi = JSON.parse(localStorage.getItem("transaksi")) || [];

// fungsi untuk isi dropdown murid
function isiDropdownMurid() {
    const select = document.getElementById("pilihMurid");

    select.innerHTML = "";

    murid.forEach((m) => {
        const option = document.createElement("option");

        option.value = m.id;
        option.textContent = m.nama;

        select.appendChild(option);
    })
}

// fungsi tambah transaksi
function tambahTransaksi() {
    const idMurid = document.getElementById("pilihMurid").value;
    const jenis = document.getElementById("jenis").value;
    const jumlah = document.getElementById("jumlah").value
}