create table listas
(
	id int unsigned auto_increment
		primary key,
	nome varchar(192) null,
	criado_em datetime default current_timestamp() not null
);

create table mercados
(
	id int unsigned auto_increment
		primary key,
	nome varchar(255) null,
	deletado_em datetime null
);

create table carrinhos
(
	id int unsigned auto_increment
		primary key,
	mercado int unsigned null,
	criado_em datetime default current_timestamp() not null,
	constraint carrinhos_mercados_id_fk
		foreign key (mercado) references mercados (id)
			on update cascade on delete set null
);

create table produtos
(
	id int unsigned auto_increment
		primary key,
	nome varchar(192) null,
	codigo varchar(64) null,
	criado_em datetime default current_timestamp() not null,
	deletado_em datetime null,
	constraint codigo
		unique (codigo),
	constraint nome
		unique (nome)
);

create table carrinho_itens
(
	id int unsigned auto_increment
		primary key,
	carrinho int unsigned not null,
	produto int unsigned not null,
	quantidade float null,
	preco decimal(6,2) null,
	criado_em datetime default current_timestamp() not null,
	constraint carrinho_itens_carrinhos_id_fk
		foreign key (carrinho) references carrinhos (id)
			on update cascade on delete cascade,
	constraint carrinho_itens_produtos_id_fk
		foreign key (produto) references produtos (id)
			on update cascade
);

create table lista_itens
(
	id int unsigned auto_increment
		primary key,
	lista int unsigned not null,
	produto int unsigned not null,
	quantidade float null,
	criado_em datetime default current_timestamp() not null,
	constraint lista_itens_listas_id_fk
		foreign key (lista) references listas (id)
			on update cascade on delete cascade,
	constraint lista_itens_produtos_id_fk
		foreign key (produto) references produtos (id)
			on update cascade
);


