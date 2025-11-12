# PHP + Apache 웹서버용 Dockerfile

# 기초 이미지(Base Image) 지정
# PHP 8.2 버전 + Apache 웹 서버("httpd")가 함께 포함된 공식 이미지
FROM php:8.2-apache

# 환경 변수 설정 Apache 서버의 루트 디렉토리를 지정
ENV APACHE_DOCUMENT_ROOT=/var/www/html

# PHP 확장 기능 과 Apache 모듈 설치하는 단계
# pdo: (DB 추상화 계층), pdo_mysql: MySQL용 PDO 드라이버
# URL Rewrite 모듈(mode rewrite) 활성화
RUN docker-php-ext-install pdo pdo_mysql \
    && a2enmod rewrite

# 로컬의 app 폴더를 컨테이너 내부의 /var/www/html(환경변수)로 복사
# => 웹사이트 전체가 Apache 서버의 루트로 들어감
COPY app ${APACHE_DOCUMENT_ROOT}

# 복사된 파일의 소유자(owner) 변경 => Apache가 PHP 파일을 안전하게 읽고 실행 가능
RUN chown -R www-data:www-data ${APACHE_DOCUMENT_ROOT}
