document.querySelectorAll(".nav").forEach(btn=>{
  btn.addEventListener("click",()=>{
    document.querySelectorAll(".nav,.page").forEach(x=>x.classList.remove("active"));
    btn.classList.add("active");
    document.getElementById(btn.dataset.page).classList.add("active");
    document.getElementById("title").textContent=btn.textContent;
    history.replaceState(null,"","#"+btn.dataset.page);
  });
});
if(location.hash){
  const target=document.querySelector(`[data-page="${location.hash.slice(1)}"]`);
  if(target) target.click();
}
document.getElementById("search")?.addEventListener("input",e=>{
  const q=e.target.value.toLowerCase();
  document.querySelectorAll("#productTable tr").forEach(row=>{
    row.style.display=row.textContent.toLowerCase().includes(q)?"":"none";
  });
});
function openEdit(p){
  document.getElementById("edit_id").value=p.id;
  document.getElementById("edit_nama").value=p.nama;
  document.getElementById("edit_kategori").value=p.kategori;
  document.getElementById("edit_harga").value=p.harga;
  document.getElementById("edit_stok").value=p.stok;
  document.getElementById("editModal").classList.add("show");
}
function closeEdit(){document.getElementById("editModal").classList.remove("show");}
