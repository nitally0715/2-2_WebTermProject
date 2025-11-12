# Node.js 기반 백엔드 API 서버용 Dockerfile

# 기초 이미지(Base Image) 지정
# node:18-alpine: Node.js 18 버전을 포함한 Alpine Linux(아주 가벼운 리눅스) 이미지.
FROM node:18-alpine

# 작업 디렉토리(폴더) 설정 ex) pwd
WORKDIR /usr/src/app

# 현재 로컬(빌드 시점)의 package.json 파일을 컨테이너 안 ./(pwd) 폴더로 복사
# npm install: 의존성 패키지들 설치
#--production: 개발용은 설치 x -> 실제 서비스에서 필요한 라이브러리만 컨테이너 크기 최소화
COPY package.json ./
RUN npm install --production

# 앱의 소스 코드 전체를 컨테이너(내부 /usr/src/app/src)에 옮김 
COPY src ./src

# 컨테이너가 포트 3000번에서 실행 -> 외부 접근은 docker run -p 3000:3000 처럼 연결
EXPOSE 3000

# 컨테이너 시작 시 실행 명령 설정 -> package.json의 start 스크립트 실행
CMD ["npm", "start"]
