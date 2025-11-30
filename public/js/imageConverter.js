

const uploadToCloudinary = async (file) => {

  const cloudName = "YOUR_CLOUD_NAME";

  const uploadPreset = "YOUR_UPLOAD_PRESET";


  const formData = new FormData();

  formData.append("file", file);

  formData.append("upload_preset", uploadPreset);

  try {

    const response = await fetch(

      `https:


      {

        method: "POST",
        body: formData,
      },
    );

    if (!response.ok) throw new Error("Image upload failed");

    const data = await response.json();

    return data.secure_url;
  } catch (error) {

    console.error("Upload error:", error);

    this.showToast("Failed to upload image", "error");

    return null;
  }

};
