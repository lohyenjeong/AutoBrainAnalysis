# Malpem

FROM ubuntu:14.04

MAINTAINER YENJEONG LOH <LOHYENJEONG@GMAIL.COM>

# Libraries and dependencies

RUN \
  apt-get update && apt-get -y install \
  autoconf \
  build-essential \
  python \
  python-dev \
  python-distribute \
  python-pip \
  wget \
  unzip \
  zlib1g-dev


# Install AWS CLI 
RUN \
  pip install awscli


# Compile and install Malpem
RUN \
  cd

RUN \
  wget -O malpem_installer.tar http://www.doc.ic.ac.uk/~cl6311/Material/MALPEM/malpem_installer.tar

RUN \
  tar xf malpem_installer.tar

RUN \
  yes | ./malpem_installer/malpem-install /opt/malpem-1.2 


COPY malpem2102.sh /opt/malpem-1.2/

WORKDIR /opt/malpem-1.2

CMD [ "./malpem2102.sh" ]
