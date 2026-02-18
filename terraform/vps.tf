resource "digitalocean_droplet" "cmpe272" {
    image = "ubuntu-22-04-x64"
    name = "cmpe272"
    region = "sfo2"
    size = "s-1vcpu-512mb-10gb"
    ssh_keys = [
      data.digitalocean_ssh_key.desktop.id
    ]

  connection {
    host = self.ipv4_address
    user = "root"
    type = "ssh"
    private_key = file(var.pvt_key)
    timeout = "2m"
  }
  
  provisioner "remote-exec" {
    inline = [
      "export PATH=$PATH:/usr/bin",
      "sudo apt update",
      "sudo apt install -y apache2"
    ]
  }
}
