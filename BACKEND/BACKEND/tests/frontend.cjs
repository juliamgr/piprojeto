const vm=require('node:vm');const fs=require('node:fs');const assert=require('node:assert/strict');const crypto=require('node:crypto');
const root=require('node:path').join(__dirname,'../');
class Element {
 constructor(){this.innerHTML='';this.textContent='';this.hidden=true;this.disabled=false;this.value='';this.style={};this.handlers={};this.dataset={};this.company={value:''};}
 addEventListener(type,fn){this.handlers[type]=fn;}
 setAttribute(){} scrollIntoView(){} querySelector(){return this.button||(this.button=new Element());}
 reset(){this.didReset=true;}
}
const elements=new Map();const el=s=>{if(!elements.has(s))elements.set(s,new Element());return elements.get(s)};
const handlers={};const storage=()=>{const m=new Map();return{getItem:k=>m.get(k)||null,setItem:(k,v)=>m.set(k,String(v))}};
const calls=[];let failOrder=true;
const product={id:1,name:'Alface <img src=x onerror=alert(1)>',category:'Folhas',producer:'Horta & Campo',location:'Porto Alegre',unit:'unidade',price:4.5,stock:2,image:'img/produto-placeholder.svg',imagePosition:'center',badge:''};
const c={console,Intl,JSON,Number,String,Object,Array,Math,Uint8Array,setTimeout,crypto:crypto.webcrypto,localStorage:storage(),sessionStorage:storage(),alert:()=>{},
 document:{querySelector:el,querySelectorAll:()=>[],addEventListener:(k,f)=>{(handlers[k]??=[]).push(f)},body:{style:{}},documentElement:{style:{}}},
 FormData:class {constructor(form){this.form=form}get(k){return this.form.values[k]}},
 fetch:async(url,options={})=>{
  let data,ok=true;
  if(url==='api/session.php')data={csrfToken:'token',shipping:{fee:9.9,freeFrom:120}};
  if(url==='api/products.php')data={products:[product,{...product,id:2,name:'Esgotado',stock:0}]};
  if(url==='api/orders.php'){calls.push(JSON.parse(options.body));assert.equal(options.headers['X-CSRF-Token'],'token');if(failOrder){ok=false;failOrder=false;data={message:'Estoque insuficiente'}}else data={order:{code:'AG-TESTE',total:18.9}};}
  if(url==='api/newsletter.php')data={message:'Inscrição registrada'};
  return{ok,json:async()=>data};
 }};
c.window=c;vm.createContext(c);vm.runInContext(fs.readFileSync(root+'script.js','utf8'),c);
const run=s=>vm.runInContext(s,c);const flush=()=>new Promise(r=>setImmediate(r));
(async()=>{
 await flush();
 assert.ok(el('#product-grid').innerHTML.includes('&lt;img src=x onerror=alert(1)&gt;'));
 assert.ok(!el('#product-grid').innerHTML.includes('<img src=x'));
 assert.match(el('#product-grid').innerHTML,/data-id="2" disabled/);
 run('changeCart(1, 100)');assert.equal(run('state.cart[1]'),2);
 run('openCart();state.checkoutStep="details";renderCart()');
 const form=new Element();form.id='checkout-form';form.values={customerName:'Cliente Teste',customerEmail:'teste@example.com',customerPhone:'51999999999',deliveryAddress:'Rua Exemplo, 123',deliveryRegion:'Porto Alegre',paymentMethod:'pix'};
 const evt={target:form,preventDefault(){}};
 await handlers.submit[0](evt);assert.equal(run('state.cart[1]'),2);assert.equal(form.button.disabled,false);
 await handlers.submit[0](evt);await flush();
 assert.equal(calls.length,2);assert.equal(calls[0].requestId,calls[1].requestId);assert.deepEqual(calls[1].items,[{productId:1,quantity:2}]);
 assert.equal(run('state.checkoutStep'),'success');assert.equal(c.localStorage.getItem('agrolink-cart'),'{}');
 assert.ok(el('#cart-content').innerHTML.includes('AG-TESTE'));
 const newsletter=el('#newsletter-form');el('#newsletter-email').value='test@example.com';const event={currentTarget:newsletter,preventDefault(){}};
 const promise=newsletter.handlers.submit(event);event.currentTarget=null;await promise;
 assert.equal(newsletter.didReset,true);
 console.log('PASS: escape de HTML, item esgotado, limite de quantidade, falha preserva sacola, token CSRF, requestId estável no reenvio, contrato de itens, limpeza após sucesso e newsletter após await.');
 console.log('Execução em DOM simulado (Node VM), sem navegador, PHP ou MySQL.');
})().catch(err=>{console.error(err);process.exit(1)});
