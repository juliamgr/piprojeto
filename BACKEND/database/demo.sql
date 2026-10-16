USE agrolink;
START TRANSACTION;
INSERT INTO products (name,category,producer,location,unit,price_cents,stock,min_stock,badge,batch_code,expires_on) VALUES
('Cesta da estação','Cestas','Produtor de demonstração','Porto Alegre','cesta · 6 itens',3990,12,5,'Da estação','DEMO-01',DATE_ADD(CURRENT_DATE,INTERVAL 5 DAY)),
('Alface crespa','Folhas','Horta de demonstração','Viamão','unidade',450,20,8,'Colheita local','DEMO-02',DATE_ADD(CURRENT_DATE,INTERVAL 2 DAY)),
('Banana prata','Frutas','Sítio de demonstração','Guaíba','pacote · 1 kg',790,15,5,'','DEMO-03',DATE_ADD(CURRENT_DATE,INTERVAL 6 DAY)),
('Tomate','Legumes','Horta de demonstração','Canoas','pacote · 500 g',590,4,5,'','DEMO-04',DATE_ADD(CURRENT_DATE,INTERVAL 3 DAY));
INSERT INTO stock_movements(product_id,type,quantity,balance_after,reason)
SELECT id,'entrada',stock,stock,'Carga inicial de demonstração' FROM products WHERE batch_code LIKE 'DEMO-%';
COMMIT;
