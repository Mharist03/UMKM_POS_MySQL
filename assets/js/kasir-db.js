let cart=[];
const money=n=>"Rp"+Number(n||0).toLocaleString("id-ID");

function addCart(p){
  const found=cart.find(x=>x.id===p.id);
  if(found){
    if(found.qty<p.stok) found.qty++;
  } else cart.push({...p,qty:1});
  renderCart();
}
function changeQty(id,d){
  const c=cart.find(x=>x.id===id);
  if(!c)return;
  c.qty+=d;
  if(c.qty<=0)cart=cart.filter(x=>x.id!==id);
  else if(c.qty>c.stok)c.qty=c.stok;
  renderCart();
}
function renderCart(){
  const total=cart.reduce((s,x)=>s+x.harga*x.qty,0);
  document.getElementById("cartItems").innerHTML=cart.length?cart.map(x=>`
    <div class="cart-row"><span><b>${escapeHtml(x.nama)}</b><br><small>${money(x.harga)}</small></span>
    <span class="qty"><button type="button" onclick="changeQty(${x.id},-1)">−</button> ${x.qty} <button type="button" onclick="changeQty(${x.id},1)">+</button></span></div>`).join(""):`<div class="empty">Keranjang kosong.</div>`;
  document.getElementById("cartTotal").textContent=money(total);
  document.getElementById("hiddenItems").innerHTML=`<input type="hidden" name="items" value='${JSON.stringify(cart)}'>`;
  updateChange();
}
function updateChange(){
  const total=cart.reduce((s,x)=>s+x.harga*x.qty,0);
  const paid=Number(document.getElementById("paid").value)||0;
  document.getElementById("change").textContent=money(Math.max(0,paid-total));
}
function escapeHtml(s){const d=document.createElement("div");d.textContent=s;return d.innerHTML;}
document.getElementById("paid").addEventListener("input",updateChange);
document.getElementById("productSearch").addEventListener("input",e=>{
  const q=e.target.value.toLowerCase();
  document.querySelectorAll(".product").forEach(x=>x.style.display=x.dataset.name.includes(q)?"":"none");
});
document.getElementById("checkoutForm").addEventListener("submit",e=>{
  const total=cart.reduce((s,x)=>s+x.harga*x.qty,0);
  const paid=Number(document.getElementById("paid").value)||0;
  if(!cart.length){e.preventDefault();alert("Keranjang kosong.");}
  else if(paid<total){e.preventDefault();alert("Uang pembayaran kurang.");}
});
const params=new URLSearchParams(location.search);
if(params.get("success")) alert("Transaksi berhasil. Kembalian: "+money(params.get("change")));
if(params.get("error")) alert("Transaksi gagal: "+params.get("error"));
renderCart();
