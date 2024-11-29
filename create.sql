create database PanoramaViewerWithMarkersDB;

use PanoramaViewerWithMarkersDB;

create table users(
    username varchar(255) not null,
    userpassword varchar(255) not null,
    primary key (username)
);

create table panoramaimages(
    username varchar(255) not null,
    imagenumber int not null,
    imagedata longblob not null,
    constraint pkimage primary key (username, imagenumber)
);

create table markers(
    username varchar(255) not null,
    imagenumber int not null,
    pitch double not null,
    yaw double not null,
    textdata text,
    imagedata longblob,
    constraint pkmarker primary key (username, imagenumber, pitch, yaw)
);